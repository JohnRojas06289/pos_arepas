<?php

namespace App\Listeners;

use App\Enums\TipoTransaccionEnum;
use App\Events\CreateVentaDetalleEvent;
use App\Models\Kardex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateRegistroVentaCardexListener
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
        $kardex = new Kardex();

        $ultimoRegistro = $kardex->where('producto_id', $event->producto_id)
            ->lockForUpdate()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        $costoUnitario = $ultimoRegistro ? $ultimoRegistro->costo_unitario : $event->precio_venta;

        $kardex->crearRegistro(
            [
                'venta_id' => $event->venta->id,
                'producto_id' => $event->producto_id,
                'cantidad' => $event->cantidad,
                'costo_unitario' => $costoUnitario,
            ],
            TipoTransaccionEnum::Venta
        );
    }
}
