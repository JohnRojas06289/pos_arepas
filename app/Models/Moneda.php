<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Moneda extends Model
{
    use HasUuids;
    public function empresa(): HasOne
    {
        return $this->hasOne(Empresa::class);
    }
}
