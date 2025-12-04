<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Empresa extends Model
{
    use HasUuids;
    protected $guarded = ['id'];

    protected $table = 'empresa';

    public function moneda(): BelongsTo
    {
        return $this->belongsTo(Moneda::class);
    }
}
