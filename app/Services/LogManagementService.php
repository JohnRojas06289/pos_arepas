<?php

namespace App\Services;

use App\Enums\TipoTransaccionEnum;
use App\Models\Inventario;
use App\Models\Kardex;
use App\Models\Movimiento;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class LogManagementService
{
    /**
     * Revierte una venta y restaura el inventario de cada producto.
     */
    public static function reverseVenta(string $ventaId, string|int $userId): array
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

            $kardex = new Kardex();

            foreach ($venta->productos as $producto) {
                $cantidad   = $producto->pivot->cantidad;
                $inventario = Inventario::where('producto_id', $producto->id)->first();

                if (!$inventario) {
                    throw new \Exception("No se encontró inventario para el producto: {$producto->nombre}");
                }

                $inventario->increment('cantidad', $cantidad);

                $ultimoKardex = Kardex::where('producto_id', $producto->id)->latest('id')->first();
                $kardex->crearRegistro(
                    [
                        'producto_id'    => $producto->id,
                        'venta_id'       => $venta->id,
                        'cantidad'       => $cantidad,
                        'costo_unitario' => $ultimoKardex ? $ultimoKardex->costo_unitario : 0,
                    ],
                    TipoTransaccionEnum::Reversa
                );
            }

            $venta->revertida = 1;
            $venta->save();

            // Eliminar el Movimiento de caja y recalcular saldo
            $movimiento = Movimiento::where('descripcion', 'Venta n° ' . $venta->numero_comprobante)->first();
            if ($movimiento) {
                $caja = $movimiento->caja;
                $movimiento->delete();

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

            ActivityLogService::log('Reversión de venta', 'Ventas', [
                'venta_id'              => $ventaId,
                'numero_comprobante'    => $venta->numero_comprobante,
                'total'                 => $venta->total,
                'productos_restaurados' => $venta->productos->count(),
                'movimiento_eliminado'  => $movimiento?->id,
            ]);

            DB::commit();

            return ['success' => true, 'message' => 'Venta revertida exitosamente. El inventario y el saldo de caja han sido restaurados.'];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al revertir venta', ['venta_id' => $ventaId, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Error al revertir la venta: ' . $e->getMessage()];
        }
    }
}
