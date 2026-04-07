<?php

namespace App\Http\Controllers;

use App\Enums\MetodoPagoEnum;
use App\Events\CreateVentaDetalleEvent;
use App\Events\CreateVentaEvent;
use App\Http\Requests\StoreVentaRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\ActivityLogService;
use App\Services\ComprobanteService;
use App\Services\EmpresaService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ventaController extends Controller
{
    protected EmpresaService $empresaService;

    public function __construct(EmpresaService $empresaService)
    {
        $this->middleware('permission:ver-venta|crear-venta|mostrar-venta|eliminar-venta', ['only' => ['index']]);
        $this->middleware('permission:crear-venta', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-venta', ['only' => ['show']]);
        $this->middleware('check-caja-aperturada-user', ['only' => ['create', 'store']]);
        $this->middleware('check-show-venta-user', ['only' => ['show']]);
        $this->empresaService = $empresaService;
    }

    /**
     * Listado de ventas (ultimos 90 dias por defecto).
     * El DataTable client-side maneja busqueda, orden y paginacion.
     */
    public function index(Request $request): View
    {
        $desde = $request->input('desde', now()->subDays(90)->toDateString());
        $hasta = $request->input('hasta', now()->toDateString());

        $ventas = Venta::with(['comprobante', 'cliente.persona', 'user'])
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->latest()
            ->get();

        return view('venta.index', compact('ventas', 'desde', 'hasta'));
    }

    /**
     * Formulario del Punto de Venta.
     */
    public function create(ComprobanteService $comprobanteService): View|RedirectResponse
    {
        $empresa = $this->empresaService->obtenerEmpresa();

        $productos = Producto::leftJoin('inventario as i', 'i.producto_id', '=', 'productos.id')
            ->leftJoin('presentaciones as p', 'p.id', '=', 'productos.presentacione_id')
            ->select(
                DB::raw("COALESCE(p.sigla, 'UND') as sigla"),
                'productos.nombre',
                'productos.codigo',
                'productos.id',
                DB::raw("COALESCE(i.cantidad, 0) as cantidad"),
                'productos.precio',
                'productos.img_path',
                'productos.categoria_id'
            )
            ->where('productos.estado', 1)
            ->orderBy('productos.nombre', 'asc')
            ->get();

        $categorias = Cache::remember('categorias_activas_sorted', 3600, function () {
            return Categoria::with('caracteristica')
                ->join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
                ->where('c.estado', 1)
                ->orderBy('c.nombre', 'asc')
                ->select('categorias.*')
                ->get();
        });

        $comprobantes = $comprobanteService->obtenerComprobantes();
        $optionsMetodoPago = array_values(array_filter(
            MetodoPagoEnum::cases(),
            fn (MetodoPagoEnum $metodo) => $metodo !== MetodoPagoEnum::Fiado
        ));

        return view('venta.create', compact(
            'productos',
            'categorias',
            'comprobantes',
            'optionsMetodoPago',
            'empresa'
        ));
    }

    /**
     * Registrar una nueva venta.
     */
    public function store(StoreVentaRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $productoIds = $validated['arrayidproducto'];
        $cantidades = $validated['arraycantidad'];
        $preciosVenta = $validated['arrayprecioventa'];
        $ventaData = Arr::only($validated, [
            'cliente_id',
            'comprobante_id',
            'metodo_pago',
            'subtotal',
            'total',
            'monto_recibido',
            'vuelto_entregado',
        ]);

        try {
            $venta = null;

            for ($attempt = 0; $attempt < 3; $attempt++) {
                try {
                    DB::beginTransaction();

                    $venta = Venta::create($ventaData);

                    foreach ($productoIds as $index => $productoId) {
                        $venta->productos()->syncWithoutDetaching([
                            $productoId => [
                                'id' => Str::uuid()->toString(),
                                'cantidad' => (int) $cantidades[$index],
                                'precio_venta' => $preciosVenta[$index],
                            ],
                        ]);

                        CreateVentaDetalleEvent::dispatch(
                            $venta,
                            $productoId,
                            (int) $cantidades[$index],
                            (float) $preciosVenta[$index]
                        );
                    }

                    CreateVentaEvent::dispatch($venta);
                    DB::commit();
                    break;
                } catch (QueryException $e) {
                    DB::rollBack();

                    if ($this->isNumeroComprobanteConflict($e) && $attempt < 2) {
                        usleep(50000);
                        continue;
                    }

                    throw $e;
                } catch (Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            ActivityLogService::log('Creacion de una venta', 'Ventas', [
                'venta_id' => $venta?->id,
                'cliente_id' => $venta?->cliente_id,
                'total' => $venta?->total,
                'productos' => count($productoIds),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Venta registrada con exito',
                    'venta_id' => $venta?->id,
                    'numero_comprobante' => $venta?->numero_comprobante,
                    'total' => $venta?->total,
                    'show_url' => $venta ? route('ventas.show', $venta) : null,
                ]);
            }

            return redirect()->route('pos.index')->with('success', 'Venta registrada');
        } catch (Throwable $e) {
            Log::error('Error al crear la venta', [
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->route('pos.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Detalle de una venta.
     */
    public function show(Venta $venta): View
    {
        $empresa = $this->empresaService->obtenerEmpresa();

        return view('venta.show', compact('venta', 'empresa'));
    }

    private function isNumeroComprobanteConflict(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'numero_comprobante')
            && (str_contains($message, 'unique') || str_contains($message, 'duplicate'));
    }
}
