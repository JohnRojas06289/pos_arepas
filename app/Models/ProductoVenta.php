<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductoVenta extends Model
{
    use HasUuids;

    protected $table = 'producto_venta';
    protected $guarded = ['id'];
}
