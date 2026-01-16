<?php

namespace App\Http\Controllers;

use App\Enums\MetodoPagoEnum;
use App\Events\CreateVentaDetalleEvent;
use App\Events\CreateVentaEvent;
use App\Http\Requests\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\ActivityLogService;
use App\Services\ComprobanteService;
use App\Services\EmpresaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ventaController extends Controller
{
    protected EmpresaService $empresaService;

    function __construct(EmpresaService $empresaService)
    {
        $this->middleware('permission:ver-venta|crear-venta|mostrar-venta|eliminar-venta', ['only' => ['index']]);
        $this->middleware('permission:crear-venta', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-venta', ['only' => ['show']]);
        //$this->middleware('permission:eliminar-venta', ['only' => ['destroy']]);
        $this->middleware('check-caja-aperturada-user', ['only' => ['create', 'store']]);
        $this->middleware('check-show-venta-user', ['only' => ['show']]);
        $this->empresaService = $empresaService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $ventas = Venta::with(['comprobante', 'cliente.persona', 'user'])
            ->latest()
            ->get();

        return view('venta.index', compact('ventas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(ComprobanteService $comprobanteService): View|RedirectResponse
    {
        // Verificar que existe una empresa
        $empresa = $this->empresaService->obtenerEmpresa();
        
        // Verificar que existen clientes
        $clientes = Cliente::whereHas('persona', function ($query) {
            $query->where('estado', 1);
        })->get();
        
        if ($clientes->isEmpty()) {
            return redirect()->route('panel')
                ->with('error', 'Debe crear al menos un cliente antes de realizar ventas. Vaya a Clientes > Nuevo Cliente.');
        }

        // Verificar productos (sin bloquear si está vacío)
        $productos = Producto::join('inventario as i', function ($join) {
            $join->on('i.producto_id', '=', 'productos.id');
        })
            ->leftJoin('presentaciones as p', function ($join) {
                $join->on('p.id', '=', 'productos.presentacione_id');
            })
            ->select(
                DB::raw("COALESCE(p.sigla, 'UND') as sigla"),
                'productos.nombre',
                'productos.codigo',
                'productos.id',
                'i.cantidad',
                'productos.precio',
                'productos.img_path',
                'productos.categoria_id'
            )
            ->where('productos.estado', 1)
            ->where('i.cantidad', '>', 0)
            ->get();

        // ELIMINADO EL BLOQUEO DE INVENTARIO VACÍO POR SOLICITUD DEL USUARIO
        
        $categorias = Categoria::whereHas('caracteristica', function ($query) {
            $query->where('estado', 1);
        })->get();

        $comprobantes = $comprobanteService->obtenerComprobantes();
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
     * Store a newly created resource in storage.
     */
    public function store(StoreVentaRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            //Llenar mi tabla venta
            $venta = Venta::create($request->validated());

            //Llenar mi tabla venta_producto
            //1. Recuperar los arrays
            $arrayProducto_id = $request->get('arrayidproducto');
            $arrayCantidad = $request->get('arraycantidad');
            $arrayPrecioVenta = $request->get('arrayprecioventa');

            //2.Realizar el llenado
            $siseArray = count($arrayProducto_id);
            $cont = 0;

            while ($cont < $siseArray) {
                $venta->productos()->syncWithoutDetaching([
                    $arrayProducto_id[$cont] => [
                        'id' => \Illuminate\Support\Str::uuid()->toString(),
                        'cantidad' => $arrayCantidad[$cont],
                        'precio_venta' => $arrayPrecioVenta[$cont],
                    ]
                ]);

                //Despachar evento
                CreateVentaDetalleEvent::dispatch(
                    $venta,
                    $arrayProducto_id[$cont],
                    $arrayCantidad[$cont],
                    $arrayPrecioVenta[$cont]
                );

                $cont++;
            }

            //Despachar evento
            CreateVentaEvent::dispatch($venta);

            DB::commit();
            ActivityLogService::log('Creación de una venta', 'Ventas', $request->validated());
            return redirect()->route('ventas.create')
                ->with('success', 'Venta registrada');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear la venta', ['error' => $e->getMessage()]);
            return redirect()->route('ventas.create')->with('error', 'Ups, algo falló: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta): View
    {
        $empresa =  $this->empresaService->obtenerEmpresa();
        return view('venta.show', compact('venta', 'empresa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        /* Venta::where('id', $id)
            ->update([
                'estado' => 0
            ]);

        return redirect()->route('ventas.index')->with('success', 'Venta eliminada');*/
    }
}
