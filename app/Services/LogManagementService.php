<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class LogManagementService
{
    /**
     * Revierte una venta y restaura el inventario de cada producto.
     */
    public static function reverseVenta(string $ventaId, int $userId): array
    {
        try {
            DB::beginTransaction();

            $venta = Venta::with('productos')->find($ventaId);

            if (!$venta) {
                return ['success' => false, 'message' => 'Venta no encontrada'];
            }

            if ($venta->revertida) {
                return ['success' => false, 'message' => 'Esta venta ya fue revertida anteriormente'];
            }

            foreach ($venta->productos as $producto) {
                $cantidad   = $producto->pivot->cantidad;
                $inventario = Inventario::where('producto_id', $producto->id)->first();

                if (!$inventario) {
                    throw new \Exception("No se encontró inventario para el producto: {$producto->nombre}");
                }

                $inventario->increment('cantidad', $cantidad);
            }

            $venta->revertida = true;
            $venta->save();

            ActivityLogService::log('Reversión de venta', 'Ventas', [
                'venta_id'              => $ventaId,
                'numero_comprobante'    => $venta->numero_comprobante,
                'total'                 => $venta->total,
                'productos_restaurados' => $venta->productos->count(),
            ]);

            DB::commit();

            return ['success' => true, 'message' => 'Venta revertida exitosamente. El inventario ha sido restaurado.'];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al revertir venta', ['venta_id' => $ventaId, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Error al revertir la venta: ' . $e->getMessage()];
        }
    }
}
