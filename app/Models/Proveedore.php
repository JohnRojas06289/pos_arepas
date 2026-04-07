<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedore extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['persona_id'];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class);
    }

    /**
     * Obtener la razon social, tipo y número de documento del proveedor
     */
    public function getNombreDocumentoAttribute(): string
    {
        $razonSocial = $this->persona?->razon_social ?? 'Proveedor sin nombre';
        $documento   = $this->persona?->documento?->nombre ?? 'Documento';
        $numero      = $this->persona?->numero_documento ?? 'Sin número';

        return "{$razonSocial} - {$documento}: {$numero}";
    }
}
