<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ubicacione extends Model
{
    use HasUuids;
    protected $fillable = ['nombre'];
    
    public function inventario(): HasMany
    {
        return $this->hasMany(Inventario::class);
    }
}
