<?php

namespace App\Observers;

use App\Models\Caja;
use App\Models\Comprobante;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VentaObsever
{
    /**
     * Handle the Venta "creating" event.
     */
    public function creating(Venta $venta): void
    {
        try {
            $caja = Caja::where('user_id', Auth::id())->where('estado', 1)->first();

            if (!$caja) {
                Log::error('No hay caja abierta para el usuario', ['user_id' => Auth::id()]);
                throw new \Exception('No hay una caja abierta para este usuario');
            }

            $tipoComprobante = Comprobante::findOrFail($venta->comprobante_id)->nombre;

            if ($venta->metodo_pago === \App\Enums\MetodoPagoEnum::Fiado->value) {
                $venta->pagado = false;
                $venta->saldo_pendiente = round(max(0, (float) $venta->total - (float) $venta->monto_recibido), 2);
            } else {
                $venta->pagado = true;
                $venta->saldo_pendiente = 0;
            }

            $venta->user_id            = Auth::id();
            $venta->caja_id            = $caja->id;
            $venta->numero_comprobante = $venta->generarNumeroVenta($caja->id, $tipoComprobante);
            $venta->fecha_hora         = Carbon::now()->toDateTimeString();
        } catch (\Exception $e) {
            Log::error('Error en VentaObserver::creating', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            throw $e;
        }
    }
}
