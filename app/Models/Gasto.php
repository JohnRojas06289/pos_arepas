<?php

namespace App\Models;

use App\Enums\CategoriaGastoEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gasto extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'fecha'     => 'date',
        'categoria' => CategoriaGastoEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFechaFormateadaAttribute(): string
    {
        return Carbon::parse($this->fecha)->format('d-m-Y');
    }
}
