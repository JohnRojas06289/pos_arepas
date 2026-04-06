<?php

namespace App\Listeners;

use App\Enums\TipoMovimientoEnum;
use App\Events\CreateVentaEvent;
use App\Models\Movimiento;
use Illuminate\Support\Facades\Log;

class CreateMovimientoVentaCajaListener
{
    public function __construct()
    {
    }

    public function handle(CreateVentaEvent $event): void
    {
        if ($event->venta->metodo_pago === \App\Enums\MetodoPagoEnum::Fiado->value) {
            return;
        }

        if (!$event->venta->pagado) {
            return;
        }

        try {
            Movimiento::create([
                'tipo' => TipoMovimientoEnum::Venta,
                'descripcion' => 'Venta n° ' . $event->venta->numero_comprobante,
                'monto' => $event->venta->total,
                'metodo_pago' => $event->venta->metodo_pago,
                'caja_id' => $event->venta->caja_id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en el Listener CreateMovimientoVentaCajaListener', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
