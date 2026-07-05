<?php

namespace App\Http\Controllers;

use App\Enums\CategoriaGastoEnum;
use App\Enums\MetodoPagoEnum;
use App\Events\CreateCompraDetalleEvent;
use App\Models\Compra;
use App\Models\Gasto;
use App\Models\Producto;
use App\Models\Proveedore;
use App\Services\ActivityLogService;
use App\Services\ComprobanteService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GastoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-gasto')->only('index');
        $this->middleware('permission:crear-gasto')->only('create', 'store');
        $this->middleware('permission:eliminar-gasto')->only('destroy');
    }

    public function index(): View
    {
        $gastos = Gasto::where('user_id', Auth::id())
            ->with(['compra.productos'])
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->get();

        $categorias      = CategoriaGastoEnum::cases();
        $optionsMetodoPago = MetodoPagoEnum::cases();

        // Totales resumen
        $hoy = now()->toDateString();

        $totalHoy = $gastos->filter(fn($g) => $g->fecha->toDateString() === $hoy)->sum('monto');

        return view('gasto.index', compact(
            'gastos', 'categorias', 'optionsMetodoPago', 'totalHoy'
        ));
    }

    public function create(ComprobanteService $comprobanteService): View
    {
        $categorias        = CategoriaGastoEnum::cases();
        $optionsMetodoPago = MetodoPagoEnum::cases();
        $productos         = Producto::where('estado', 1)->orderBy('nombre')->get();
        $proveedores       = Proveedore::with('persona.documento')
            ->whereHas('persona', fn($q) => $q->where('estado', 1))
            ->get();
        $comprobantes      = $comprobanteService->obtenerComprobantes();

        return view('gasto.create', compact(
            'categorias', 'optionsMetodoPago', 'productos', 'proveedores', 'comprobantes'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'categoria'   => 'required|string',
            'descripcion' => 'nullable|string|max:255',
            'fecha'       => 'required|date',
            'metodo_pago' => 'nullable|string',
            'notas'       => 'nullable|string|max:1000',
        ]);

        if ($request->categoria === 'SURTIDO') {
            return $this->storeSurtido($request);
        }

        // ── Gasto normal ─────────────────────────────────────────────────
        $request->validate([
            'monto'       => 'required|numeric|min:1',
            'comprobante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        try {
            $comprobantePath = null;
            if ($request->hasFile('comprobante')) {
                $file = $request->file('comprobante');
                $name = uniqid() . '.' . $file->getClientOriginalExtension();
                $comprobantePath = $file->storeAs('gastos', $name, config('filesystems.default'));
            }

            $gasto = Gasto::create([
                'user_id'          => Auth::id(),
                'categoria'        => $request->categoria,
                'descripcion'      => $request->descripcion,
                'monto'            => $request->monto,
                'fecha'            => $request->fecha,
                'metodo_pago'      => $request->metodo_pago,
                'comprobante_path' => $comprobantePath,
                'notas'            => $request->notas,
            ]);

            ActivityLogService::log('Registro de gasto', 'Gastos', ['gasto_id' => $gasto->id, 'monto' => $gasto->monto]);

            return redirect()->route('gastos.index')->with('success', 'Gasto registrado correctamente');
        } catch (Throwable $e) {
            Log::error('Error al registrar gasto', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Ups, algo falló al guardar el gasto');
        }
    }

    private function storeSurtido(Request $request): RedirectResponse
    {
        $request->validate([
            'arrayidproducto'       => 'required|array|min:1',
            'arrayidproducto.*'     => 'required|exists:productos,id',
            'arraycantidad'         => 'required|array|min:1',
            'arraycantidad.*'       => 'required|numeric|min:1',
            'arraypreciocompra'     => 'required|array|min:1',
            'arraypreciocompra.*'   => 'required|numeric|min:0',
            'arrayfechavencimiento' => 'nullable|array',
            'proveedore_id'         => 'nullable|exists:proveedores,id',
            'comprobante_id'        => 'nullable|exists:comprobantes,id',
            'numero_comprobante'    => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $productoIds       = $request->get('arrayidproducto', []);
            $cantidades        = $request->get('arraycantidad', []);
            $preciosCompra     = $request->get('arraypreciocompra', []);
            $fechasVencimiento = $request->get('arrayfechavencimiento', []);
            $total             = 0;

            foreach ($cantidades as $i => $cant) {
                $total += (float) $cant * (float) ($preciosCompra[$i] ?? 0);
            }

            $compra = Compra::create([
                'user_id'            => Auth::id(),
                'proveedore_id'      => $request->proveedore_id,
                'comprobante_id'     => $request->comprobante_id,
                'numero_comprobante' => $request->numero_comprobante,
                'fecha_hora'         => $request->fecha . 'T' . now()->format('H:i'),
                'subtotal'           => $total,
                'total'              => $total,
                'impuesto'           => 0,
            ]);

            foreach ($productoIds as $i => $productoId) {
                $cantidad        = $cantidades[$i] ?? 0;
                $precioCompra    = $preciosCompra[$i] ?? 0;
                $fechaVencimiento = $fechasVencimiento[$i] ?? null;

                $compra->productos()->syncWithoutDetaching([
                    $productoId => [
                        'id'               => Str::uuid()->toString(),
                        'cantidad'         => $cantidad,
                        'precio_compra'    => $precioCompra,
                        'fecha_vencimiento'=> $fechaVencimiento,
                    ],
                ]);

                CreateCompraDetalleEvent::dispatch(
                    $compra,
                    $productoId,
                    $cantidad,
                    $precioCompra,
                    $fechaVencimiento
                );
            }

            $gasto = Gasto::create([
                'user_id'     => Auth::id(),
                'categoria'   => 'SURTIDO',
                'descripcion' => $request->descripcion,
                'monto'       => $total,
                'fecha'       => $request->fecha,
                'metodo_pago' => $request->metodo_pago,
                'notas'       => $request->notas,
                'compra_id'   => $compra->id,
            ]);

            DB::commit();
            ActivityLogService::log('Registro de surtido', 'Gastos', [
                'gasto_id' => $gasto->id,
                'compra_id' => $compra->id,
                'monto' => $total,
            ]);

            return redirect()->route('gastos.index')->with('success', 'Surtido registrado correctamente');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al registrar surtido', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Ups, algo falló al guardar el surtido');
        }
    }

    public function scanFactura(Request $request): JsonResponse
    {
        $request->validate([
            'imagen' => 'required|file|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return response()->json(['error' => '⚠️ La IA no está configurada. Verifica GEMINI_API_KEY en Heroku.'], 503);
        }

        $file     = $request->file('imagen');
        $mimeType = $file->getMimeType();
        $base64   = base64_encode(file_get_contents($file->getRealPath()));

        // Prompt minimalista — solo extrae de la imagen, sin lista de productos
        $prompt = 'Analiza esta factura o tiquete de compra. Extrae TODOS los productos con cantidad y precio unitario. '
            . 'Si el precio en la factura es el total de la línea, divídelo entre la cantidad para obtener el precio unitario. '
            . 'No inventes productos que no estén en la imagen. '
            . 'Responde ÚNICAMENTE con JSON válido sin markdown: '
            . '{"productos":[{"nombre":"nombre del producto","cantidad":2,"precio_unitario":1500}]}';

        try {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

            $response = Http::timeout(20)->post($url, [
                'contents' => [[
                    'parts' => [
                        ['text' => $prompt],
                        ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64]],
                    ],
                ]],
                'generationConfig' => [
                    'temperature'      => 0.1,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if (!$response->successful()) {
                Log::error('Gemini scan-factura error', ['status' => $response->status(), 'body' => substr($response->body(), 0, 500)]);
                return response()->json(['error' => '❌ Error de la IA (código ' . $response->status() . '). Intenta nuevamente.'], 500);
            }

            $body = $response->json();
            $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) {
                return response()->json(['error' => '❌ La IA no pudo leer la imagen. Intenta con una foto más clara y bien iluminada.'], 422);
            }

            $text = preg_replace('/```json\s*|\s*```/', '', trim($text));
            $data = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data['productos'])) {
                return response()->json(['error' => '❌ No se detectaron productos. Asegúrate de que la factura sea legible.'], 422);
            }

            // Hacer match con productos del sistema en PHP
            $sistemaProdutos = Producto::where('estado', 1)->get(['id', 'nombre']);

            $resultado = array_map(function ($p) use ($sistemaProdutos) {
                $nombreFactura = $p['nombre'] ?? '';
                $mejorId       = null;
                $mejorNombre   = null;
                $mejorScore    = 0;

                foreach ($sistemaProdutos as $sp) {
                    similar_text(
                        mb_strtolower($nombreFactura),
                        mb_strtolower($sp->nombre),
                        $pct
                    );
                    if ($pct > $mejorScore) {
                        $mejorScore  = $pct;
                        $mejorId     = $sp->id;
                        $mejorNombre = $sp->nombre;
                    }
                }

                return [
                    'id'             => $mejorScore >= 50 ? $mejorId : null,
                    'nombre_factura' => $nombreFactura,
                    'nombre_sistema' => $mejorScore >= 50 ? $mejorNombre : null,
                    'cantidad'       => (float) ($p['cantidad'] ?? 1),
                    'precio_unitario'=> (float) ($p['precio_unitario'] ?? 0),
                ];
            }, $data['productos']);

            return response()->json(['productos' => $resultado]);

        } catch (Throwable $e) {
            Log::error('Error scan-factura', ['error' => $e->getMessage()]);
            return response()->json(['error' => '❌ Error interno: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Gasto $gasto): RedirectResponse
    {
        try {
            if ($gasto->comprobante_path) {
                Storage::delete($gasto->comprobante_path);
            }
            $gasto->delete();
            ActivityLogService::log('Eliminación de gasto', 'Gastos', ['gasto_id' => $gasto->id]);

            return redirect()->route('gastos.index')->with('success', 'Gasto eliminado');
        } catch (Throwable $e) {
            Log::error('Error al eliminar gasto', ['error' => $e->getMessage()]);
            return redirect()->route('gastos.index')->with('error', 'Ups, algo falló');
        }
    }
}
