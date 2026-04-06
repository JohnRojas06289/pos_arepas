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
        $registro = Inventario::where('producto_id', $event->producto_id)
            ->lockForUpdate()
            ->first();

        if (!$registro) {
            $registro = Inventario::create([
                'producto_id' => $event->producto_id,
                'ubicacione_id' => null,
                'cantidad' => 0,
                'fecha_vencimiento' => $event->fecha_vencimiento,
            ]);
        }

        $registro->increment('cantidad', (int) $event->cantidad);
        $registro->update(['fecha_vencimiento' => $event->fecha_vencimiento]);
    }
}
