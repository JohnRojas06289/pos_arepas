<?php

namespace App\Observers;

use App\Models\Caja;
use App\Models\Comprobante;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentaObsever
{
    /**
     * Handle the Caja "creating" event.
     */
    public function creating(Venta $venta): void
    {
        try {
            $caja = Caja::where('user_id', Auth::id())->where('estado', 1)->first();
            
            if (!$caja) {
                \Log::error('No hay caja abierta para el usuario', ['user_id' => Auth::id()]);
                throw new \Exception('No hay una caja abierta para este usuario');
            }
            
            $tipoComprobante = Comprobante::findOrFail($venta->comprobante_id)->nombre;
            
            // Si el método de pago es FIADO la venta queda pendiente de cobro
            if ($venta->metodo_pago === \App\Enums\MetodoPagoEnum::Fiado->value) {
                // Use DB::raw so Eloquent inserts literal SQL 'false' without PDO param binding
                $venta->pagado = DB::raw('false');
                $venta->saldo_pendiente = $venta->total - $venta->monto_recibido;
            } else {
                $venta->pagado = DB::raw('true');
                $venta->saldo_pendiente = 0;
            }

            $venta->user_id = Auth::id();
            $venta->caja_id = $caja->id;
            $venta->numero_comprobante = $venta->generarNumeroVenta($caja->id, $tipoComprobante);
            $venta->fecha_hora = Carbon::now()->toDateTimeString();
            
            \Log::info('Venta preparada para crear', [
                'user_id' => $venta->user_id,
                'caja_id' => $venta->caja_id,
                'numero_comprobante' => $venta->numero_comprobante,
                'total' => $venta->total
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en VentaObserver::creating', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            throw $e;
        }
    }

    /**
     * Handle the Venta "created" event.
     */
    public function created(Venta $venta): void
    {
        //
    }

    /**
     * Handle the Venta "updated" event.
     */
    public function updated(Venta $venta): void
    {
        //
    }

    /**
     * Handle the Venta "deleted" event.
     */
    public function deleted(Venta $venta): void
    {
        //
    }

    /**
     * Handle the Venta "restored" event.
     */
    public function restored(Venta $venta): void
    {
        //
    }

    /**
     * Handle the Venta "force deleted" event.
     */
    public function forceDeleted(Venta $venta): void
    {
        //
    }
}
