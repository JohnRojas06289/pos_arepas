<?php

namespace App\Models;

use App\Observers\VentaObsever;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

#[ObservedBy(VentaObsever::class)]
class Venta extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'cliente_id',
        'comprobante_id',
        'numero_comprobante',
        'metodo_pago',
        'fecha_hora',
        'subtotal',
        'total',
        'monto_recibido',
        'vuelto_entregado',
        'pagado',
        'saldo_pendiente',
        'revertida',
    ];

    protected $casts = [
        'pagado' => 'boolean',
        'revertida' => 'boolean',
        'saldo_pendiente' => 'decimal:2',
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class);
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class)
            ->withTimestamps()
            ->withPivot('cantidad', 'precio_venta');
    }

    public function scopePagadas(Builder $query): Builder
    {
        return $query->where('pagado', 1);
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('pagado', 0);
    }

    public function scopeNoRevertidas(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->where('revertida', false)
                ->orWhereNull('revertida');
        });
    }

    public function getFechaAttribute(): string
    {
        return Carbon::parse($this->fecha_hora)->format('d-m-Y');
    }

    public function getHoraAttribute(): string
    {
        return Carbon::parse($this->fecha_hora)->format('H:i');
    }

    public function generarNumeroVenta(string $cajaId, string $tipoComprobante): string
    {
        $prefijo = strtoupper(substr($tipoComprobante, 0, 1));

        $ultimoNumero = DB::table('ventas')
            ->where('numero_comprobante', 'like', $prefijo . '-%')
            ->lockForUpdate()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->value('numero_comprobante');

        $ultimoConsecutivo = 0;
        if (is_string($ultimoNumero) && preg_match('/^[A-Z]-(\d+)$/', $ultimoNumero, $matches) === 1) {
            $ultimoConsecutivo = (int) $matches[1];
        }

        return sprintf('%s-%07d', $prefijo, $ultimoConsecutivo + 1);
    }
}
