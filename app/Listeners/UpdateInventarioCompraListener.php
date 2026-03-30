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
                \Log::warning('UpdateInventarioCompraListener: Inventario not found, creating new record', ['producto_id' => $event->producto_id]);
                $registro = Inventario::create([
                    'producto_id'       => $event->producto_id,
                    'cantidad'          => $event->cantidad,
                    'fecha_vencimiento' => $event->fecha_vencimiento,
                ]);
                \Log::info('UpdateInventarioCompraListener: Inventario record created', [
                    'producto_id' => $event->producto_id,
                    'cantidad'    => $event->cantidad,
                ]);
                return;
            }

            $cantidadAnterior = $registro->cantidad;
            $registro->increment('cantidad', $event->cantidad);
            $registro->update(['fecha_vencimiento' => $event->fecha_vencimiento]);

            \Log::info('UpdateInventarioCompraListener: Stock updated', [
                'producto_id' => $event->producto_id,
                'cantidad_anterior' => $cantidadAnterior,
                'cantidad_nueva' => $registro->cantidad
            ]);
        } catch (\Exception $e) {
            \Log::error('UpdateInventarioCompraListener: Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
