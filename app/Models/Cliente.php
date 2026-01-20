<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['persona_id', 'tipo_cliente'];

    protected $casts = [
        'tipo_cliente' => 'string',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    /**
     * Check if client is general (cash) type
     */
    public function isGeneral(): bool
    {
        return $this->tipo_cliente === 'general';
    }

    /**
     * Check if client is fiado (credit) type
     */
    public function isFiado(): bool
    {
        return $this->tipo_cliente === 'fiado';
    }

    /**
     * Get pending balance for credit clients
     */
    public function getSaldoPendiente(): float
    {
        return $this->ventas()
            ->where('pagado', false)
            ->sum('total');
    }

     /**
     * Obtener la razon social, tipo y número de documento del cliente
     * @return string
     */
    public function getNombreDocumentoAttribute(): string
    {
        return $this->persona->razon_social . ' - ' . $this->persona->documento->nombre . ': ' . $this->persona->numero_documento;
    }
    
}
