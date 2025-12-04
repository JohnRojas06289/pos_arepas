<?php

namespace App\Models;

use App\Observers\VentaObsever;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy(VentaObsever::class)]
class Venta extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

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

     /**
     * Obtener solo la fecha
     * @return string
     */
    public function getFechaAttribute(): string
    {
        return Carbon::parse($this->fecha_hora)->format('d-m-Y');
    }

    /**
     * Obtener solo la hora
     * @return string
     */
    public function getHoraAttribute(): string
    {
        return Carbon::parse($this->fecha_hora)->format('H:i');
    }


    /**
     * Generar el número de venta
     */
    public function generarNumeroVenta(string $cajaId, string $tipoComprobante): string
    {
        // Determinar el prefijo según el tipo de comprobante
        $prefijo = strtoupper(substr($tipoComprobante, 0, 1)); // "B" para Boleta, "F" para Factura

        // Contar el total de ventas globalmente (no solo por caja) para evitar duplicados
        $totalVentas = Venta::count();
        
        // Incrementar el número
        $nuevoNumero = $totalVentas + 1;

        // Formatear el número de venta (Prefijo + Número secuencial de 7 dígitos)
        $numeroVenta = sprintf("%s-%07d", $prefijo, $nuevoNumero);

        return $numeroVenta;
    }
}
