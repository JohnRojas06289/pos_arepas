<?php

namespace App\Listeners;

use App\Events\CreateVentaDetalleEvent;
use App\Models\Inventario;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateInventarioVentaListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CreateVentaDetalleEvent $event): void
    {
        try {
            \Log::info('UpdateInventarioVentaListener: Updating stock', [
                'producto_id' => $event->producto_id,
                'cantidad_vendida' => $event->cantidad
            ]);

            $registro = Inventario::where('producto_id', $event->producto_id)->first();

            if (!$registro) {
                \Log::error('UpdateInventarioVentaListener: Inventario not found', ['producto_id' => $event->producto_id]);
                return;
            }

            $nuevaCantidad = $registro->cantidad - $event->cantidad;
            
            $registro->update(['cantidad' => $nuevaCantidad]);
            
            \Log::info('UpdateInventarioVentaListener: Stock updated', [
                'producto_id' => $event->producto_id,
                'cantidad_anterior' => $registro->cantidad,
                'cantidad_nueva' => $nuevaCantidad
            ]);
        } catch (\Exception $e) {
            \Log::error('UpdateInventarioVentaListener: Error', ['error' => $e->getMessage()]);
        }
    }
}
