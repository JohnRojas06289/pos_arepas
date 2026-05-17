<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CierreInventario extends Model
{
    protected $table = 'cierre_inventario';

    protected $fillable = [
        'caja_id',
        'user_id',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
