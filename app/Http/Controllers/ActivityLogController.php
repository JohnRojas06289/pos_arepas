<?php

namespace App\Http\Controllers;

use App\Enums\MetodoPagoEnum;
use App\Enums\TipoTransaccionEnum;
use App\Models\ActivityLog;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Inventario;
use App\Models\Kardex;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\ActivityLogService;
use App\Services\LogManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-registro-actividad', ['only' => ['index', 'show']]);
        $this->middleware('permission:eliminar-registro-actividad', ['only' => ['destroy']]);
        $this->middleware('permission:revertir-venta', ['only' => ['reverseVenta']]);
    }

    public function index(Request $request): View
    {
        $modulo = $request->input('modulo', 'todos');
        $desde  = $request->input('desde', now()->subDays(30)->toDateString());
        $hasta  = $request->input('hasta', now()->toDateString());

        $query = ActivityLog::with('user')
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->latest();

        if ($modulo !== 'todos') {
            $query->where('module', $modulo);
        }

        $activityLogs = $query->paginate(50)->withQueryString();

        // Precargar ventas y compras en batch para evitar N+1
        $logs = $activityLogs->getCollection();

        $ventaIds  = $logs->map(fn($l) => $l->getVentaId())->filter()->values()->all();
        $compraIds = $logs->map(fn($l) => $l->getCompraId())->filter()->values()->all();

        $ventasMap  = $ventaIds  ? Venta::with('productos')->whereIn('id', $ventaIds)->get()->keyBy('id')  : collect();
        $comprasMap = $compraIds ? Compra::with('productos')->whereIn('id', $compraIds)->get()->keyBy('id') : collect();

        $logs->each(function ($log) use ($ventasMap, $comprasMap) {
            $log->ventaCargada  = $ventasMap->get($log->getVentaId());
            $log->compraCargada = $comprasMap->get($log->getCompraId());
        });

        $modulos = ActivityLog::select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->filter()
            ->values();

        return view('activityLog.index', compact('activityLogs', 'modulo', 'desde', 'hasta', 'modulos'));
    }

    public function show(string $id): View
    {
        $log    = ActivityLog::with('user')->findOrFail($id);
        $venta  = null;
        $compra = null;

        if ($log->isVentaLog()) {
            // Primero buscar por venta_id guardado en el log (logs nuevos)
            if ($log->getVentaId()) {
                $venta = Venta::with(['productos.presentacione', 'cliente.persona', 'comprobante', 'user'])
                    ->find($log->getVentaId());
            }
            // Fallback por proximidad de timestamp para logs anteriores (sin venta_id guardado)
            if (!$venta) {
                Log::warning('ActivityLog: usando fallback por timestamp para encontrar venta', [
                    'log_id'    => $log->id,
                    'user_id'   => $log->user_id,
                    'log_time'  => $log->created_at,
                ]);
                $venta = Venta::with(['productos.presentacione', 'cliente.persona', 'comprobante', 'user'])
                    ->where('user_id', $log->user_id)
                    ->whereBetween('created_at', [
                        $log->created_at->copy()->subSeconds(5),
                        $log->created_at->copy()->addSeconds(5),
                    ])
                    ->first();
            }
        }

        if ($log->isCompraLog()) {
            if ($log->getCompraId()) {
                $compra = Compra::with(['productos', 'proveedore.persona', 'comprobante', 'user'])
                    ->find($log->getCompraId());
            }
        }

        $clientes    = $log->isVentaLog() ? Cliente::with('persona')->whereHas('persona', fn($q) => $q->where('estado', 1))->get() : collect();
        $metodosPago = MetodoPagoEnum::cases();
        $productos   = $log->isVentaLog() ? Producto::orderBy('nombre')->get() : collect();

        return view('activityLog.show', compact('log', 'venta', 'compra', 'clientes', 'metodosPago', 'productos'));
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $log = ActivityLog::findOrFail($id);

            ActivityLogService::log('Eliminación de registro de actividad', 'Registro de actividad', [
                'deleted_log_id' => $id,
                'action'         => $log->action,
                'module'         => $log->module,
            ]);

            $log->delete();

            return redirect()->route('activityLog.index')->with('success', 'Registro eliminado correctamente');
        } catch (Throwable $e) {
            Log::error('Error al eliminar registro de actividad', ['error' => $e->getMessage()]);
            return redirect()->route('activityLog.index')->with('error', 'Error al eliminar el registro');
        }
    }

    public function reverseVenta(string $logId): RedirectResponse
    {
        try {
            $log = ActivityLog::findOrFail($logId);

            if (!$log->isVentaLog()) {
                return redirect()->back()->with('error', 'Este registro no corresponde a una venta');
            }

            // Buscar por venta_id guardado en el log (logs nuevos)
            $ventaId = $log->getVentaId();

            // Fallback por proximidad de timestamp para logs anteriores (sin venta_id guardado)
            if (!$ventaId) {
                Log::warning('ActivityLog: usando fallback por timestamp para revertir venta', [
                    'log_id'   => $log->id,
                    'user_id'  => $log->user_id,
                    'log_time' => $log->created_at,
                ]);
                $venta = Venta::where('user_id', $log->user_id)
                    ->whereBetween('created_at', [
                        $log->created_at->copy()->subSeconds(5),
                        $log->created_at->copy()->addSeconds(5),
                    ])
                    ->first();
                $ventaId = $venta?->id;
            }

            if (!$ventaId) {
                return redirect()->back()->with('error', 'No se pudo encontrar la venta asociada');
            }

            $result = LogManagementService::reverseVenta($ventaId, Auth::id());

            return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
        } catch (Throwable $e) {
            Log::error('Error al revertir venta desde log', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al revertir la venta');
        }
    }

    /**
     * Editar campos de una venta: metodo_pago, cliente y productos/cantidades (solo admin).
     * No ajusta inventario — solo actualiza la venta y la tabla pivote.
     */
    public function updateVenta(Request $request, string $logId): RedirectResponse
    {
        try {
            if (!Auth::user()->hasRole('administrador')) {
                return redirect()->back()->with('error', 'Solo el administrador puede editar ventas');
            }

            $log = ActivityLog::findOrFail($logId);

            if (!$log->isVentaLog()) {
                return redirect()->back()->with('error', 'Este registro no corresponde a una venta');
            }

            $ventaId = $log->getVentaId();
            if (!$ventaId) {
                return redirect()->back()->with('error', 'No se puede editar: venta no vinculada directamente');
            }

            $venta = Venta::with('productos')->findOrFail($ventaId);

            $validated = $request->validate([
                'metodo_pago'              => 'required|string',
                'cliente_id'               => 'nullable|exists:clientes,id',
                'productos'                => 'required|array|min:1',
                'productos.*.producto_id'  => 'required|exists:productos,id',
                'productos.*.cantidad'     => 'required|integer|min:1',
                'productos.*.precio_venta' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            // ── Capturar cantidades ANTES del sync ───────────────────────────
            $oldQtys = $venta->productos->keyBy('id')
                ->map(fn($p) => (int) $p->pivot->cantidad);

            // ── Aplicar sync de productos ────────────────────────────────────
            $syncData = [];
            foreach ($validated['productos'] as $prod) {
                $syncData[$prod['producto_id']] = [
                    'cantidad'     => $prod['cantidad'],
                    'precio_venta' => $prod['precio_venta'],
                ];
            }
            $venta->productos()->sync($syncData);

            // ── Calcular nuevo total ─────────────────────────────────────────
            $nuevoTotal = collect($validated['productos'])
                ->sum(fn($p) => $p['cantidad'] * $p['precio_venta']);

            // ── Ajustar Inventario y Kardex por diferencias de cantidad ──────
            $newQtys = collect($validated['productos'])
                ->keyBy('producto_id')
                ->map(fn($p) => (int) $p['cantidad']);

            $allIds = $oldQtys->keys()->merge($newQtys->keys())->unique();
            $kardexModel = new Kardex();

            foreach ($allIds as $pid) {
                $oldQty = $oldQtys->get($pid, 0);
                $newQty = $newQtys->get($pid, 0);
                $diff   = $oldQty - $newQty; // >0 = devolver stock, <0 = quitar más

                if ($diff === 0) continue;

                $inventario = Inventario::where('producto_id', $pid)->first();
                if (!$inventario) continue;

                if ($diff > 0) {
                    $inventario->increment('cantidad', $diff);
                } else {
                    $inventario->decrement('cantidad', abs($diff));
                }

                $ultimoKardex = Kardex::where('producto_id', $pid)->latest('id')->first();
                $kardexModel->crearRegistro(
                    [
                        'producto_id'    => $pid,
                        'venta_id'       => $venta->id,
                        'cantidad'       => abs($diff),
                        'costo_unitario' => $ultimoKardex?->costo_unitario ?? 0,
                    ],
                    $diff > 0 ? TipoTransaccionEnum::Reversa : TipoTransaccionEnum::Venta
                );
            }

            // ── Actualizar Movimiento de caja y recalcular saldo ─────────────
            $movimiento = Movimiento::where('descripcion', 'Venta n° ' . $venta->numero_comprobante)->first();
            $caja = null;
            if ($movimiento) {
                $caja = $movimiento->caja;
                $movimiento->monto       = $nuevoTotal;
                $movimiento->metodo_pago = $validated['metodo_pago'];
                $movimiento->save();

                if ($caja) {
                    $totales = Movimiento::where('caja_id', $caja->id)
                        ->selectRaw("
                            SUM(CASE WHEN tipo = 'VENTA' THEN monto ELSE 0 END) AS total_venta,
                            SUM(CASE WHEN tipo = 'RETIRO' THEN monto ELSE 0 END) AS total_retiro
                        ")->first();
                    $caja->saldo_final = $caja->saldo_inicial
                        + ($totales->total_venta  ?? 0)
                        - ($totales->total_retiro ?? 0);
                    $caja->saveQuietly();
                }
            }

            // ── Actualizar Venta ─────────────────────────────────────────────
            $venta->update([
                'metodo_pago' => $validated['metodo_pago'],
                'cliente_id'  => $validated['cliente_id'],
                'subtotal'    => $nuevoTotal,
                'total'       => $nuevoTotal,
            ]);

            ActivityLogService::log('Edición de venta (admin)', 'Ventas', [
                'venta_id'           => $venta->id,
                'numero_comprobante' => $venta->numero_comprobante,
                'cambios'            => [
                    'metodo_pago'          => $validated['metodo_pago'],
                    'cliente_id'           => $validated['cliente_id'],
                    'nuevo_total'          => $nuevoTotal,
                    'productos'            => count($validated['productos']),
                    'inventario_ajustado'  => $allIds->count(),
                ],
            ]);

            DB::commit();

            return redirect()->route('activityLog.show', $logId)->with('success', 'Venta actualizada correctamente. Inventario y saldo de caja sincronizados.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al editar venta desde log', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al actualizar la venta');
        }
    }
}
