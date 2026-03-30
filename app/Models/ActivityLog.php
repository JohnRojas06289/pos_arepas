<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasUuids;
    protected $fillable = ['user_id', 'action', 'module', 'data', 'ip_address'];

    protected $casts = [
        'data' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return Carbon::parse($this->attributes['created_at'])->format('d/m/Y H:i');
    }

    /**
     * Verifica si este log corresponde a la creación de una venta.
     */
    public function isVentaLog(): bool
    {
        return $this->action === 'Creación de una venta' && $this->module === 'Ventas';
    }

    public function isCompraLog(): bool
    {
        return $this->action === 'Creación de compra' && $this->module === 'Compras';
    }

    public function getVentaId(): ?string
    {
        return $this->data['venta_id'] ?? null;
    }

    public function getCompraId(): ?string
    {
        return $this->data['compra_id'] ?? null;
    }

    /**
     * Resumen compacto para mostrar en la tabla del index.
     * Extrae total, cantidad y precio desde el data JSON sin queries adicionales.
     */
    public function getResumenAttribute(): array
    {
        $data = $this->data ?? [];

        if ($this->isVentaLog() || ($this->module === 'Ventas' && isset($data['total']))) {
            return [
                'tipo'     => 'venta',
                'total'    => $data['total'] ?? null,
                'metodo'   => $data['metodo_pago'] ?? null,
            ];
        }

        if ($this->isCompraLog() || ($this->module === 'Compras' && isset($data['total']))) {
            return [
                'tipo'   => 'compra',
                'total'  => $data['total'] ?? null,
                'metodo' => $data['metodo_pago'] ?? null,
            ];
        }

        if (isset($data['cantidad'])) {
            return [
                'tipo'         => 'inventario',
                'cantidad'     => $data['cantidad'],
                'precio_venta' => $data['precio_venta'] ?? null,
                'costo'        => $data['costo_unitario'] ?? null,
            ];
        }

        return ['tipo' => 'otro'];
    }
}
