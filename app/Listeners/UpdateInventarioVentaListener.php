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
                throw new \Exception("Inventario no encontrado para producto: {$event->producto_id}. La venta no puede procesarse sin registro de inventario.");
            }

            $cantidadAnterior = $registro->cantidad;
            $registro->decrement('cantidad', $event->cantidad);

            \Log::info('UpdateInventarioVentaListener: Stock updated', [
                'producto_id' => $event->producto_id,
                'cantidad_anterior' => $cantidadAnterior,
                'cantidad_nueva' => $cantidadAnterior - $event->cantidad,
            ]);
        } catch (\Exception $e) {
            \Log::error('UpdateInventarioVentaListener: Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
