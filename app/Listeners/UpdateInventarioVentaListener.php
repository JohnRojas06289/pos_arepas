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
        $registro = Inventario::where('producto_id', $event->producto_id)
            ->lockForUpdate()
            ->first();

        if (!$registro) {
            throw new \RuntimeException('No existe inventario inicializado para el producto vendido.');
        }

        if ((int) $registro->cantidad < (int) $event->cantidad) {
            throw new \RuntimeException('Stock insuficiente para completar la venta.');
        }

        $registro->decrement('cantidad', (int) $event->cantidad);
        $registro->refresh();
    }
}
