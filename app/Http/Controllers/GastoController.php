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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'descripcion' => 'required|string|max:255',
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
                'metodo_pago'        => $request->metodo_pago,
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
