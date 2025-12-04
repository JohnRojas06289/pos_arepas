<?php

namespace App\Listeners;

use App\Events\CreateVentaEvent;
use App\Jobs\EnviarComprobanteVentaJob;

class EnviarEmailClienteVentaListener
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(CreateVentaEvent $event): void
    {
        // Deshabilitado temporalmente - requiere configuración de servidor de correo y tabla jobs con UUIDs
        // EnviarComprobanteVentaJob::dispatch($event->venta->id)->afterCommit();
    }
}
