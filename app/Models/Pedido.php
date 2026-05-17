<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    protected $fillable = [
        'user_id',
        'items',
        'total',
        'estado',
        'nombre_tomador',
    ];

    protected $casts = [
        'items'  => 'array',
        'estado' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
