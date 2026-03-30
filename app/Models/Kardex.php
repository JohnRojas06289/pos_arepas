<?php

namespace App\Models;

use App\Enums\TipoTransaccionEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kardex extends Model
{
    use HasUuids;
    protected $guarded = ['id'];

    protected $table = 'kardex';

    protected $casts = ['tipo_transaccion' => TipoTransaccionEnum::class];

    private const MARGEN_GANANCIA = 0.2;

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Kardex::class);
    }

    public function getFechaAttribute(): string
    {
        return $this->created_at->format('d/m/Y');
    }

    public function getHoraAttribute(): string
    {
        return $this->created_at->format('h:i A');
    }

    public function getCostoTotalAttribute(): float
    {
        return $this->saldo * $this->costo_unitario;
    }

    /**
     * Crear un registro en el Kardex
     */
    public function crearRegistro(array $data, TipoTransaccionEnum $tipo): void
    {
        $entrada = null;
        $salida = null;
        $saldo = null;

        $ultimoRegistro = $this->where('producto_id', $data['producto_id'])
            ->latest('id')
            ->lockForUpdate()
            ->first();

        $saldo = $ultimoRegistro ? $ultimoRegistro->saldo : 0;

        if ($tipo == TipoTransaccionEnum::Apertura) {
            $saldo = $data['cantidad'];
        } elseif ($tipo == TipoTransaccionEnum::Compra || $tipo == TipoTransaccionEnum::Reversa) {
            $entrada = $data['cantidad'];
            $saldo += $entrada;
        } elseif ($tipo == TipoTransaccionEnum::Venta || $tipo == TipoTransaccionEnum::Ajuste) {
            $salida = $data['cantidad'];
            $saldo -= $salida;
        }

        $this->create([
            'producto_id' => $data['producto_id'],
            'tipo_transaccion' => $tipo,
            'descripcion_transaccion' => $this->getDescripcionTransaccion($data, $tipo),
            'entrada' => $entrada,
            'salida' => $salida,
            'saldo' => $saldo,
            'costo_unitario' => $data['costo_unitario'],
        ]);
    }

    /**
     * Obtener la descripción según el tipo de Transacción
     */
    private function getDescripcionTransaccion(array $data, TipoTransaccionEnum $tipo): string
    {
        $descripcion = '';
        switch ($tipo) {
            case TipoTransaccionEnum::Apertura:
                $descripcion = 'Apertura del producto';
                break;
            case TipoTransaccionEnum::Compra:
                $descripcion = 'Entrada de producto por la compra n°' . $data['compra_id'];
                break;
            case TipoTransaccionEnum::Venta:
                $descripcion = 'Salida de producto por la venta n°' . $data['venta_id'];
                break;
            case TipoTransaccionEnum::Ajuste:
                $descripcion = 'Ajuste de producto';
                break;
            case TipoTransaccionEnum::Reversa:
                $descripcion = 'Reversión de la venta n°' . ($data['venta_id'] ?? '');
                break;
        }

        return $descripcion;
    }

    /**
     * Obtener el precio de Venta según el costo del Producto
     */
    public function calcularPrecioVenta(string $producto_id): float
    {
        $costoUltimoRegistro = $this->where('producto_id', $producto_id)
            ->latest('id')
            ->first()
            ->costo_unitario;

        return $costoUltimoRegistro + round($costoUltimoRegistro * self::MARGEN_GANANCIA, 2);
    }
}
