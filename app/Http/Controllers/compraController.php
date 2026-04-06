<?php

namespace App\Http\Controllers;

use App\Enums\MetodoPagoEnum;
use App\Events\CreateCompraDetalleEvent;
use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedore;
use App\Services\ActivityLogService;
use App\Services\ComprobanteService;
use App\Services\EmpresaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class compraController extends Controller
{
    protected EmpresaService $empresaService;

    public function __construct(EmpresaService $empresaService)
    {
        $this->middleware('permission:ver-compra|crear-compra|mostrar-compra|eliminar-compra', ['only' => ['index']]);
        $this->middleware('permission:crear-compra', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-compra', ['only' => ['show']]);
        $this->middleware('check-show-compra-user', ['only' => ['show']]);
        $this->empresaService = $empresaService;
    }

    public function index(): View
    {
        $compras = Compra::with('comprobante', 'proveedore.persona')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('compra.index', compact('compras'));
    }

    public function create(ComprobanteService $comprobanteService): View
    {
        $proveedores = Proveedore::whereHas('persona', fn ($q) => $q->where('estado', 1))->get();
        $comprobantes = $comprobanteService->obtenerComprobantes();
        $productos = Producto::where('estado', 1)->get();
        $optionsMetodoPago = MetodoPagoEnum::cases();
        $empresa = $this->empresaService->obtenerEmpresa();

        return view('compra.create', compact(
            'proveedores',
            'comprobantes',
            'productos',
            'optionsMetodoPago',
            'empresa'
        ));
    }

    public function store(StoreCompraRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $productoIds = $validated['arrayidproducto'];
        $cantidades = $validated['arraycantidad'];
        $preciosCompra = $validated['arraypreciocompra'];
        $fechasVencimiento = $validated['arrayfechavencimiento'] ?? [];

        DB::beginTransaction();

        try {
            $compraModel = new Compra();

            $comprobantePath = $request->hasFile('file_comprobante')
                ? $compraModel->handleUploadFile($request->file('file_comprobante'))
                : null;

            $compraData = Arr::only($validated, [
                'proveedore_id',
                'comprobante_id',
                'numero_comprobante',
                'metodo_pago',
                'fecha_hora',
                'subtotal',
                'total',
            ]);

            $compra = Compra::create(array_merge($compraData, [
                'user_id' => Auth::id(),
                'impuesto' => 0,
                'comprobante_path' => $comprobantePath,
            ]));

            foreach ($productoIds as $index => $productoId) {
                $compra->productos()->syncWithoutDetaching([
                    $productoId => [
                        'id' => Str::uuid()->toString(),
                        'cantidad' => (int) $cantidades[$index],
                        'precio_compra' => $preciosCompra[$index],
                        'fecha_vencimiento' => $fechasVencimiento[$index] ?? null,
                    ],
                ]);

                CreateCompraDetalleEvent::dispatch(
                    $compra,
                    $productoId,
                    (int) $cantidades[$index],
                    (float) $preciosCompra[$index],
                    $fechasVencimiento[$index] ?? null
                );
            }

            DB::commit();

            ActivityLogService::log('Creación de compra', 'Compras', [
                'compra_id' => $compra->id,
                'proveedore_id' => $compra->proveedore_id,
                'total' => $compra->total,
                'productos' => count($productoIds),
            ]);

            return redirect()->route('compras.index')->with('success', 'Compra registrada con éxito');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear la compra', ['error' => $e->getMessage()]);

            return redirect()->route('compras.index')->with('error', $e->getMessage());
        }
    }

    public function show(Compra $compra): View
    {
        $empresa = $this->empresaService->obtenerEmpresa();

        return view('compra.show', compact('compra', 'empresa'));
    }
}
