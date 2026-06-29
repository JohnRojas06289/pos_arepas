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
        return (int) DB::table('producto_venta')
            ->join('ventas', 'ventas.id', '=', 'producto_venta.venta_id')
            ->where('producto_venta.producto_id', $productoId)
            ->where('ventas.revertida', 0)
            ->whereBetween('ventas.fecha_hora', $dateRange)
            ->sum('producto_venta.cantidad');
    }

    /**
     * Calculate units purchased per product for a given date range
     */
    private function getComprasPorProducto(string $productoId, array $dateRange): int
    {
        return (int) DB::table('compra_producto')
            ->join('compras', 'compras.id', '=', 'compra_producto.compra_id')
            ->where('compra_producto.producto_id', $productoId)
            ->whereBetween('compras.created_at', $dateRange)
            ->sum('compra_producto.cantidad');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $categorias = \App\Models\Categoria::with('caracteristica')->get();
        
        // Sales period filter
        $periodo = $request->periodo ?? 'hoy';
        $periodo_compras = $request->periodo_compras ?? 'hoy';
        $dateRanges = $this->getDateRanges();
        $selectedRange = $dateRanges[$periodo] ?? $dateRanges['hoy'];
        $selectedRangeCompras = $dateRanges[$periodo_compras] ?? $dateRanges['hoy'];
        
        // Check if date filter is applied
        $fecha = $request->fecha;

        // ── Precargar ventas y compras del período seleccionado (1 query cada una) ──
        $ventasAgregadas = DB::table('producto_venta')
            ->join('ventas', 'ventas.id', '=', 'producto_venta.venta_id')
            ->where('ventas.revertida', 0)
            ->whereBetween('ventas.fecha_hora', $selectedRange)
            ->select('producto_venta.producto_id', DB::raw('SUM(producto_venta.cantidad) as total'))
            ->groupBy('producto_venta.producto_id')
            ->pluck('total', 'producto_id');

        $comprasAgregadas = DB::table('compra_producto')
            ->join('compras', 'compras.id', '=', 'compra_producto.compra_id')
            ->whereBetween('compras.created_at', $selectedRangeCompras)
            ->select('compra_producto.producto_id', DB::raw('SUM(compra_producto.cantidad) as total'))
            ->groupBy('compra_producto.producto_id')
            ->pluck('total', 'producto_id');

        if ($fecha) {
            // Kardex histórico: última entrada por producto en/antes de $fecha
            // DISTINCT ON es nativo de PostgreSQL: devuelve 1 fila por producto_id
            $kardexHistorico = DB::table('kardex')
                ->selectRaw('DISTINCT ON (producto_id) producto_id, saldo')
                ->whereDate('created_at', '<=', $fecha)
                ->orderBy('producto_id')
                ->orderByDesc('created_at')
                ->pluck('saldo', 'producto_id');

            $productos = Producto::with(['presentacione', 'categoria.caracteristica'])
                ->when($request->categoria_id, fn($q, $id) => $q->where('categoria_id', $id))
                ->orderBy('nombre', 'asc')
                ->get()
                ->map(function ($producto) use ($ventasAgregadas, $comprasAgregadas, $kardexHistorico) {
                    $saldo = $kardexHistorico[$producto->id] ?? null;

                    $producto->inventario = $saldo !== null
                        ? (object)['cantidad' => (int) $saldo, 'fecha_vencimiento' => null, 'fecha_vencimiento_format' => 'N/A (Histórico)', 'id' => null]
                        : (object)['cantidad' => 0, 'fecha_vencimiento' => null, 'fecha_vencimiento_format' => 'N/A', 'id' => null];

                    $producto->vendidos_periodo  = (int) ($ventasAgregadas[$producto->id] ?? 0);
                    $producto->comprados_periodo = (int) ($comprasAgregadas[$producto->id] ?? 0);

                    return $producto;
                });
        } else {
            // Default behavior: show current inventory
            $productos = Producto::with(['inventario', 'presentacione', 'categoria.caracteristica'])
                ->when($request->categoria_id, fn($q, $id) => $q->where('categoria_id', $id))
                ->orderBy('nombre', 'asc')
                ->get()
                ->map(function ($producto) use ($ventasAgregadas, $comprasAgregadas) {
                    $producto->vendidos_periodo  = (int) ($ventasAgregadas[$producto->id] ?? 0);
                    $producto->comprados_periodo = (int) ($comprasAgregadas[$producto->id] ?? 0);
                    return $producto;
                });
        }

        // ── Detectar divergencias entre inventario.cantidad y Kardex.saldo ──
        // Si hay productos donde ambos no coinciden, es señal de desincronización.
        $divergencias = DB::table('inventario as i')
            ->joinSub(
                DB::table('kardex')
                    ->selectRaw('DISTINCT ON (producto_id) producto_id, saldo')
                    ->orderBy('producto_id')
                    ->orderByDesc('created_at'),
                'k',
                'k.producto_id', '=', 'i.producto_id'
            )
            ->whereRaw('i.cantidad != k.saldo')
            ->select('i.producto_id', 'i.cantidad as inv_cantidad', 'k.saldo as kardex_saldo')
            ->get();

        if ($divergencias->isNotEmpty()) {
            \Log::warning('Inventario: divergencias detectadas entre inventario y Kardex', [
                'total'        => $divergencias->count(),
                'producto_ids' => $divergencias->pluck('producto_id')->all(),
                'detalle'      => $divergencias->toArray(),
            ]);
        }

        return view('inventario.index', compact('productos', 'categorias', 'periodo', 'periodo_compras', 'divergencias'));
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

        $ventas = Venta::where('revertida', 0)
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
                    'fecha' => Carbon::parse($venta->fecha_hora)->locale('es')->isoFormat('ddd D/MM/YYYY'),
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
     * Get purchases detail for a product in a given period (AJAX)
     */
    public function comprasDetalle(Request $request, string $productoId): JsonResponse
    {
        $periodo = $request->periodo ?? 'hoy';
        $dateRanges = $this->getDateRanges();
        $selectedRange = $dateRanges[$periodo] ?? $dateRanges['hoy'];

        $producto = Producto::findOrFail($productoId);

        $compras = DB::table('compra_producto')
            ->join('compras', 'compras.id', '=', 'compra_producto.compra_id')
            ->leftJoin('proveedores', 'proveedores.id', '=', 'compras.proveedore_id')
            ->leftJoin('personas', 'personas.id', '=', 'proveedores.persona_id')
            ->where('compra_producto.producto_id', $productoId)
            ->whereBetween('compras.created_at', $selectedRange)
            ->select(
                'compras.created_at',
                'compras.numero_comprobante',
                'compra_producto.cantidad',
                'compra_producto.precio_compra',
                'personas.razon_social as proveedor'
            )
            ->orderBy('compras.created_at', 'desc')
            ->get()
            ->map(function ($compra) {
                $dt = Carbon::parse($compra->created_at);
                return [
                    'fecha'           => $dt->locale('es')->isoFormat('ddd D/MM/YYYY'),
                    'hora'            => $dt->format('H:i'),
                    'proveedor'       => $compra->proveedor ?? 'N/A',
                    'cantidad'        => $compra->cantidad,
                    'precio_unitario' => number_format($compra->precio_compra, 0, ',', '.'),
                    'total'           => number_format($compra->cantidad * $compra->precio_compra, 0, ',', '.'),
                    'comprobante'     => $compra->numero_comprobante ?? 'N/A',
                ];
            });

        $periodoLabels = ['hoy' => 'Hoy', 'ayer' => 'Ayer', 'semana' => 'Esta Semana', 'mes' => 'Este Mes'];

        return response()->json([
            'producto'        => $producto->nombre,
            'periodo'         => $periodoLabels[$periodo] ?? 'Hoy',
            'total_comprados' => $compras->sum('cantidad'),
            'compras'         => $compras,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $producto = Producto::findOrFail($request->producto_id);
        $ubicaciones = Ubicacione::all();

        $inventario        = Inventario::where('producto_id', $producto->id)->first();
        $isReinitializing  = $inventario !== null;
        $ultimoKardex      = Kardex::where('producto_id', $producto->id)->latest('id')->first();

        return view('inventario.create', compact('producto', 'ubicaciones', 'isReinitializing', 'inventario', 'ultimoKardex'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventarioRequest $request, Kardex $kardex): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $inventarioExistente = Inventario::where('producto_id', $request->producto_id)->first();

            if ($inventarioExistente) {
                // REINICIALIZACIÓN: actualizar el inventario existente
                $inventarioExistente->update([
                    'cantidad'          => $request->cantidad,
                    'fecha_vencimiento' => $request->fecha_vencimiento,
                ]);
                $kardex->crearRegistro($request->validated(), TipoTransaccionEnum::Apertura);
            } else {
                // INICIALIZACIÓN NUEVA
                $kardex->crearRegistro($request->validated(), TipoTransaccionEnum::Apertura);
                Inventario::create($request->validated());
            }

            // Actualizar precio de venta del producto
            $producto = Producto::findOrFail($request->producto_id);
            $producto->update(['precio' => $request->precio_venta]);

            DB::commit();
            $msg = $inventarioExistente ? 'Producto reinicializado correctamente' : 'Producto inicializado';
            ActivityLogService::log('Inicialiación de producto', 'Productos', $request->validated());
            return redirect()->route('productos.index')->with('success', $msg);
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
    public function update(StoreInventarioRequest $request, string $id, Kardex $kardex)
    {
        $inventario = Inventario::findOrFail($id);

        DB::beginTransaction();
        try {
            $producto = $inventario->producto;
            if ($request->has('precio_venta')) {
                $producto->update(['precio' => $request->precio_venta]);
            }

            $cantidadAnterior = $inventario->cantidad;

            $data = $request->safe()->except(['costo_unitario', 'precio_venta']);
            $inventario->update($data);

            // Si la cantidad cambió, registrar el ajuste en el Kardex
            if ((int) $request->cantidad !== (int) $cantidadAnterior) {
                $costoUnitario = $request->costo_unitario
                    ?? Kardex::where('producto_id', $inventario->producto_id)->latest('id')->value('costo_unitario')
                    ?? 0;

                $kardex->crearRegistro(
                    [
                        'producto_id'    => $inventario->producto_id,
                        'cantidad'       => $request->cantidad,
                        'costo_unitario' => $costoUnitario,
                    ],
                    TipoTransaccionEnum::Apertura
                );
            }

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

    /**
     * Sincronizar inventario.cantidad con el último saldo del Kardex.
     * Acepta un producto_id específico o sincroniza todas las divergencias.
     */
    public function sincronizarKardex(Request $request): JsonResponse
    {
        try {
            $productoId = $request->input('producto_id'); // null = todos

            // Obtener últimos saldos de Kardex por producto
            $query = DB::table('kardex as k1')
                ->select('k1.producto_id', 'k1.saldo')
                ->whereRaw('k1.id = (SELECT k2.id FROM kardex k2 WHERE k2.producto_id = k1.producto_id ORDER BY k2.created_at DESC, k2.id DESC LIMIT 1)');

            if ($productoId) {
                $query->where('k1.producto_id', $productoId);
            }

            $kardexSaldos = $query->get()->keyBy('producto_id');

            if ($kardexSaldos->isEmpty()) {
                return response()->json(['error' => 'No se encontraron registros en Kardex para sincronizar.'], 422);
            }

            $actualizados = 0;
            DB::beginTransaction();

            foreach ($kardexSaldos as $pid => $kardex) {
                $rows = Inventario::where('producto_id', $pid)
                    ->update(['cantidad' => $kardex->saldo]);

                if ($rows === 0) {
                    // No existe registro de inventario — crearlo
                    Inventario::create([
                        'producto_id' => $pid,
                        'cantidad'    => $kardex->saldo,
                    ]);
                }
                $actualizados++;
            }

            DB::commit();

            ActivityLogService::log(
                'Sincronización Kardex → Inventario',
                'Inventario',
                ['productos_actualizados' => $actualizados, 'producto_id' => $productoId ?? 'todos']
            );

            return response()->json([
                'success'      => true,
                'actualizados' => $actualizados,
                'message'      => "Se sincronizaron {$actualizados} producto(s) correctamente.",
                'saldos'       => $kardexSaldos->map(fn($k) => $k->saldo)->toArray(),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al sincronizar Kardex → Inventario', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al sincronizar: ' . $e->getMessage()], 500);
        }
    }
}
