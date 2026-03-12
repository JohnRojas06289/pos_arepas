<?php

namespace App\Observers;

use App\Models\Inventario;
use App\Models\Producto;

class InventarioObserver
{
    /**
     * Handle the Inventario "created" event.
     * Al crear un registro de inventario, activa el producto asociado.
     */
    public function created(Inventario $inventario): void
    {
        Producto::where('id', $inventario->producto_id)->update(['estado' => 1]);
    }
}
