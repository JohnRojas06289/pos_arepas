<?php

namespace App\Http\Controllers;

use App\Enums\TipoTransaccionEnum;
use App\Http\Requests\StoreInventarioRequest;
use App\Models\Inventario;
use App\Models\Kardex;
use App\Models\Producto;
use App\Models\Ubicacione;
use App\Models\Venta;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class InventarioControlller extends Controller
{
    function __construct()
    {
        $this->middleware('check_producto_inicializado', ['only' => ['create', 'store']]);
    }

    /**
     * Get date ranges for sales period filters
     */
    private function getDateRanges(): array
    {
        $now = Carbon::now();
        return [
            'hoy' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'ayer' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'semana' => [$now->copy()->startOfWeek(Carbon::MONDAY), $now->copy()->endOfDay()],
            'mes' => [$now->copy()->startOfMonth(), $now->copy()->endOfDay()],
        ];
    }

    /**
     * Calculate units sold per product for a given date range
     */
    private function getVentasPorProducto(string $productoId, array $dateRange): int
    {
        return DB::table('producto_venta')
            ->join('ventas', 'ventas.id', '=', 'producto_venta.venta_id')
            ->where('producto_venta.producto_id', $productoId)
            ->where('ventas.revertida', false)
            ->whereBetween('ventas.fecha_hora', $dateRange)
            ->sum('producto_venta.cantidad');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $categorias = \App\Models\Categoria::with('caracteristica')->get();
        
        // Sales period filter
        $periodo = $request->periodo ?? 'hoy';
        $dateRanges = $this->getDateRanges();
        $selectedRange = $dateRanges[$periodo] ?? $dateRanges['hoy'];
        
        // Check if date filter is applied
        $fecha = $request->fecha;
        
        if ($fecha) {
            // When filtering by date, we need to get the last Kardex entry for each product on that date
            $productos = Producto::with(['presentacione', 'categoria.caracteristica'])
                ->when($request->categoria_id, function($query, $categoria_id) {
                    return $query->where('categoria_id', $categoria_id);
                })
                ->orderBy('nombre', 'asc')
                ->get()
                ->map(function($producto) use ($fecha, $selectedRange) {
                    // Get the last Kardex entry for this product on or before the selected date
                    $kardex = Kardex::where('producto_id', $producto->id)
                        ->whereDate('created_at', '<=', $fecha)
                        ->orderBy('created_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    
                    // Create a virtual inventario object with the historical stock
                    if ($kardex) {
                        $producto->inventario = (object)[
                            'cantidad' => $kardex->saldo,
                            'fecha_vencimiento' => null,
                            'fecha_vencimiento_format' => 'N/A (Histórico)',
                            'id' => null // No editing allowed for historical data
                        ];
                    } else {
                        // Product didn't exist or had no kardex entries on that date
                        $producto->inventario = (object)[
                            'cantidad' => 0,
                            'fecha_vencimiento' => null,
                            'fecha_vencimiento_format' => 'N/A',
                            'id' => null
                        ];
                    }
                    
                    // Add sales count for the selected period
                    $producto->vendidos_periodo = $this->getVentasPorProducto($producto->id, $selectedRange);
                    
                    return $producto;
                });
        } else {
            // Default behavior: show current inventory
            $productos = Producto::with(['inventario', 'presentacione', 'categoria.caracteristica'])
                ->when($request->categoria_id, function($query, $categoria_id) {
                    return $query->where('categoria_id', $categoria_id);
                })
                ->orderBy('nombre', 'asc')
                ->get()
                ->map(function($producto) use ($selectedRange) {
                    $producto->vendidos_periodo = $this->getVentasPorProducto($producto->id, $selectedRange);
                    return $producto;
                });
        }
            
        return view('inventario.index', compact('productos', 'categorias', 'periodo'));
    }

    /**
     * Get sales detail for a product in a given period (AJAX)
     */
    public function ventasDetalle(Request $request, string $productoId): JsonResponse
    {
        $periodo = $request->periodo ?? 'hoy';
        $dateRanges = $this->getDateRanges();
        $selectedRange = $dateRanges[$periodo] ?? $dateRanges['hoy'];

        $producto = Producto::findOrFail($productoId);

        $ventas = Venta::where('revertida', false)
            ->whereBetween('fecha_hora', $selectedRange)
            ->whereHas('productos', function ($q) use ($productoId) {
                $q->where('producto_id', $productoId);
            })
            ->with(['cliente.persona', 'user'])
            ->orderBy('fecha_hora', 'desc')
            ->get()
            ->map(function ($venta) use ($productoId) {
                $pivot = $venta->productos->firstWhere('id', $productoId)?->pivot;
                return [
                    'fecha' => Carbon::parse($venta->fecha_hora)->format('d/m/Y'),
                    'hora' => Carbon::parse($venta->fecha_hora)->format('H:i'),
                    'cliente' => $venta->cliente?->persona?->nombre ?? 'Público general',
                    'vendedor' => $venta->user?->name ?? 'N/A',
                    'cantidad' => $pivot?->cantidad ?? 0,
                    'precio_unitario' => number_format($pivot?->precio_venta ?? 0, 0, ',', '.'),
                    'total' => number_format(($pivot?->cantidad ?? 0) * ($pivot?->precio_venta ?? 0), 0, ',', '.'),
                    'comprobante' => $venta->numero_comprobante,
                ];
            });

        $periodoLabels = [
            'hoy' => 'Hoy',
            'ayer' => 'Ayer',
            'semana' => 'Esta Semana',
            'mes' => 'Este Mes',
        ];

        return response()->json([
            'producto' => $producto->nombre,
            'periodo' => $periodoLabels[$periodo] ?? 'Hoy',
            'total_vendidos' => $ventas->sum('cantidad'),
            'ventas' => $ventas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $producto = Producto::findOrfail($request->producto_id);
        $ubicaciones = Ubicacione::all();
        return view('inventario.create', compact('producto', 'ubicaciones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventarioRequest $request, Kardex $kardex): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $kardex->crearRegistro($request->validated(), TipoTransaccionEnum::Apertura);
            Inventario::create($request->validated());

            // Update product sale price
            $producto = Producto::findOrFail($request->producto_id);
            $producto->update(['precio' => $request->precio_venta]);

            DB::commit();
            ActivityLogService::log('Inicialiación de producto', 'Productos', $request->validated());
            return redirect()->route('productos.index')->with('success', 'Producto inicializado');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al inicializar el producto', ['error' => $e->getMessage()]);
            return redirect()->route('productos.index')->with('error', 'Ups, algo falló');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $inventario = Inventario::with('producto')->findOrFail($id);
        $producto = $inventario->producto;
        
        // Fetch last cost from Kardex
        $ultimoKardex = Kardex::where('producto_id', $producto->id)->latest('id')->first();
        $costo_unitario = $ultimoKardex ? $ultimoKardex->costo_unitario : 0;

        return view('inventario.edit', compact('inventario', 'producto', 'costo_unitario'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreInventarioRequest $request, string $id)
    {
        $inventario = Inventario::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Updated Product Price
            $producto = $inventario->producto;
            if ($request->has('precio_venta')) {
                $producto->update(['precio' => $request->precio_venta]);
            }

            // Update Inventory (excluding fields not in table)
            // We use except() because validated() returns fields like costo_unitario/precio_venta that don't exist in 'inventario' table
            $data = $request->safe()->except(['costo_unitario', 'precio_venta']);
            $inventario->update($data);
            
            // Note: Costo Unitario updates typically require a new Kardex entry. 
            // For now, we are prioritizing fixing the crash and price update.
            // If cost needs adjustment, a specific Kardex flow should be triggered.

            DB::commit();
            ActivityLogService::log('Actualización de inventario', 'Inventario', $request->validated());
            return redirect()->route('inventario.index')->with('success', 'Inventario actualizado');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar el inventario', ['error' => $e->getMessage()]);
            return redirect()->route('inventario.index')->with('error', 'Ups, algo falló');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $inventario = Inventario::findOrFail($id);
        DB::beginTransaction();
        try {
            $inventario->delete();
            DB::commit();
            ActivityLogService::log('Eliminación de inventario', 'Inventario', ['id' => $id]);
            return redirect()->route('inventario.index')->with('success', 'Inventario eliminado');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al eliminar el inventario', ['error' => $e->getMessage()]);
            return redirect()->route('inventario.index')->with('error', 'Ups, algo falló');
        }
    }
}
