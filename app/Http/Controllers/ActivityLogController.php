<?php

namespace App\Http\Controllers;

use App\Enums\MetodoPagoEnum;
use App\Models\ActivityLog;
use App\Models\Cliente;
use App\Models\Compra;
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
            // Fallback por proximidad de timestamp para logs anteriores
            if (!$venta) {
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

        $clientes          = $log->isVentaLog() ? Cliente::with('persona')->whereHas('persona', fn($q) => $q->where('estado', 1))->get() : collect();
        $metodosPago       = MetodoPagoEnum::cases();

        return view('activityLog.show', compact('log', 'venta', 'compra', 'clientes', 'metodosPago'));
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

            // Fallback por proximidad de timestamp para logs anteriores
            if (!$ventaId) {
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
     * Editar campos no-inventario de una venta (metodo_pago, cliente).
     */
    public function updateVenta(Request $request, string $logId): RedirectResponse
    {
        try {
            $log = ActivityLog::findOrFail($logId);

            if (!$log->isVentaLog()) {
                return redirect()->back()->with('error', 'Este registro no corresponde a una venta');
            }

            $ventaId = $log->getVentaId();
            if (!$ventaId) {
                return redirect()->back()->with('error', 'No se puede editar: venta no vinculada directamente');
            }

            $venta = Venta::findOrFail($ventaId);

            $validated = $request->validate([
                'metodo_pago' => 'required|string',
                'cliente_id'  => 'nullable|exists:clientes,id',
            ]);

            DB::beginTransaction();
            $venta->update($validated);
            ActivityLogService::log('Edición de venta', 'Ventas', [
                'venta_id'           => $venta->id,
                'numero_comprobante' => $venta->numero_comprobante,
                'cambios'            => $validated,
            ]);
            DB::commit();

            return redirect()->route('activityLog.show', $logId)->with('success', 'Venta actualizada correctamente');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al editar venta desde log', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error al actualizar la venta');
        }
    }
}
