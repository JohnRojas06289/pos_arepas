<?php

namespace App\Models;

use App\Enums\MetodoPagoEnum;
use App\Enums\TipoMovimientoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movimiento extends Model
{
    use HasUuids;
    protected $guarded = ['id'];

    protected $casts = [
        'tipo' => TipoMovimientoEnum::class,
        'metodo_pago' => MetodoPagoEnum::class
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }
}
