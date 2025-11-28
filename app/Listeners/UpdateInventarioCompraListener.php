<?php

namespace App\Listeners;

use App\Events\CreateCompraDetalleEvent;
use App\Models\Inventario;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateInventarioCompraListener
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(CreateCompraDetalleEvent $event): void
    {
        try {
            \Log::info('UpdateInventarioCompraListener: Updating stock', [
                'producto_id' => $event->producto_id,
                'cantidad_comprada' => $event->cantidad
            ]);

            $registro = Inventario::where('producto_id', $event->producto_id)->first();

            if (!$registro) {
                \Log::error('UpdateInventarioCompraListener: Inventario not found', ['producto_id' => $event->producto_id]);
                return;
            }

            $nuevaCantidad = $registro->cantidad + $event->cantidad;
            
            $registro->update([
                'cantidad' => $nuevaCantidad,
                'fecha_vencimiento' => $event->fecha_vencimiento
            ]);
            
            \Log::info('UpdateInventarioCompraListener: Stock updated', [
                'producto_id' => $event->producto_id,
                'cantidad_anterior' => $registro->cantidad,
                'cantidad_nueva' => $nuevaCantidad
            ]);
        } catch (\Exception $e) {
            \Log::error('UpdateInventarioCompraListener: Error', ['error' => $e->getMessage()]);
        }
    }
}
