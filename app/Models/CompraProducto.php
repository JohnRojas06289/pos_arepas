<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompraProducto extends Model
{
    use HasUuids;

    protected $table = 'compra_producto';
    protected $guarded = ['id'];
}
