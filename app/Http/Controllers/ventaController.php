<?php

namespace App\Http\Controllers;

use App\Enums\MetodoPagoEnum;
use App\Events\CreateVentaDetalleEvent;
use App\Events\CreateVentaEvent;
use App\Http\Requests\StoreVentaRequest;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\ActivityLogService;
use App\Services\ComprobanteService;
use App\Services\EmpresaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Listado de ventas (últimos 90 días por defecto).
     * El DataTable client-side maneja búsqueda, orden y paginación.
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

        $clientes = Cliente::join('personas', 'clientes.persona_id', '=', 'personas.id')
            ->where('personas.estado', 1)
            ->orderBy('personas.razon_social', 'asc')
            ->select('clientes.*')
            ->with('persona')
            ->get();

        if ($clientes->isEmpty()) {
            return redirect()->route('panel')
                ->with('error', 'Debe crear al menos un cliente antes de realizar ventas.');
        }

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

        $comprobantes      = $comprobanteService->obtenerComprobantes();
        $optionsMetodoPago = MetodoPagoEnum::cases();

        return view('venta.create', compact(
            'productos',
            'categorias',
            'clientes',
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
        DB::beginTransaction();
        try {
            $venta = Venta::create($request->validated());

            $productoIds     = $request->get('arrayidproducto', []);
            $cantidades      = $request->get('arraycantidad', []);
            $preciosVenta    = $request->get('arrayprecioventa', []);
            $totalProductos  = is_array($productoIds) ? count($productoIds) : 0;

            for ($i = 0; $i < $totalProductos; $i++) {
                $venta->productos()->syncWithoutDetaching([
                    $productoIds[$i] => [
                        'id'           => Str::uuid()->toString(),
                        'cantidad'     => $cantidades[$i],
                        'precio_venta' => $preciosVenta[$i],
                    ],
                ]);

                CreateVentaDetalleEvent::dispatch($venta, $productoIds[$i], $cantidades[$i], $preciosVenta[$i]);
            }

            CreateVentaEvent::dispatch($venta);
            DB::commit();

            ActivityLogService::log('Creación de una venta', 'Ventas', array_merge($request->validated(), ['venta_id' => $venta->id]));

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Venta registrada con éxito']);
            }

            return redirect()->route('ventas.create')->with('success', 'Venta registrada');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear la venta', ['error' => $e->getMessage()]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Ups, algo falló'], 500);
            }

            return redirect()->route('ventas.create')->with('error', 'Ups, algo falló');
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
}
