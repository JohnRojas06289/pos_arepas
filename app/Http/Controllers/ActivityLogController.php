<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Venta;
use App\Services\ActivityLogService;
use App\Services\LogManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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

    public function index(): View
    {
        $activityLogs = ActivityLog::with('user')->latest()->simplePaginate(50);

        // Precarga de ventas en batch para evitar N+1
        $logs = $activityLogs->getCollection();

        if ($logs->isNotEmpty()) {
            $userIds = $logs->pluck('user_id')->unique()->filter();
            $minDate = $logs->min('created_at')->copy()->subSeconds(5);
            $maxDate = $logs->max('created_at')->copy()->addSeconds(5);

            $potentialSales = Venta::with('productos')
                ->whereIn('user_id', $userIds)
                ->whereBetween('created_at', [$minDate, $maxDate])
                ->get();

            $logs->each(function ($log) use ($potentialSales) {
                if ($log->isVentaLog()) {
                    $log->venta = $potentialSales->first(function ($venta) use ($log) {
                        return $venta->user_id === $log->user_id
                            && $venta->created_at->between(
                                $log->created_at->copy()->subSeconds(5),
                                $log->created_at->copy()->addSeconds(5)
                            );
                    });
                }
            });
        }

        return view('activityLog.index', compact('activityLogs'));
    }

    public function show(string $id): View
    {
        $log   = ActivityLog::with('user')->findOrFail($id);
        $venta = null;

        if ($log->isVentaLog()) {
            $venta = Venta::with('productos')
                ->where('user_id', $log->user_id)
                ->whereBetween('created_at', [
                    $log->created_at->copy()->subSeconds(5),
                    $log->created_at->copy()->addSeconds(5),
                ])
                ->first();
        }

        return view('activityLog.show', compact('log', 'venta'));
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

            return redirect()->route('activityLog.index')->with('success', 'Registro de actividad eliminado');
        } catch (Throwable $e) {
            Log::error('Error al eliminar registro de actividad', ['error' => $e->getMessage()]);
            return redirect()->route('activityLog.index')->with('error', 'Error al eliminar el registro');
        }
    }

    /**
     * Revierte una venta asociada a un registro de actividad.
     */
    public function reverseVenta(string $logId): RedirectResponse
    {
        try {
            $log = ActivityLog::findOrFail($logId);

            if (!$log->isVentaLog()) {
                return redirect()->route('activityLog.index')
                    ->with('error', 'Este registro no corresponde a una venta');
            }

            $venta = Venta::where('user_id', $log->user_id)
                ->whereBetween('created_at', [
                    $log->created_at->copy()->subSeconds(5),
                    $log->created_at->copy()->addSeconds(5),
                ])
                ->first();

            if (!$venta) {
                return redirect()->route('activityLog.index')
                    ->with('error', 'No se pudo encontrar la venta asociada a este registro');
            }

            $result = LogManagementService::reverseVenta($venta->id, Auth::id());

            return redirect()->route('activityLog.index')
                ->with($result['success'] ? 'success' : 'error', $result['message']);
        } catch (Throwable $e) {
            Log::error('Error al revertir venta desde log', ['error' => $e->getMessage()]);
            return redirect()->route('activityLog.index')
                ->with('error', 'Error al revertir la venta');
        }
    }
}
