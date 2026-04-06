<?php

namespace App\Http\Requests;

use App\Enums\MetodoPagoEnum;
use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class StoreVentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => 'nullable|required_if:metodo_pago,FIADO|exists:clientes,id',
            'comprobante_id' => 'required|exists:comprobantes,id',
            'metodo_pago' => ['required', new Enum(MetodoPagoEnum::class)],
            'subtotal' => 'required|numeric|min:0.01',
            'total' => 'required|numeric|min:0.01',
            'monto_recibido' => 'required|numeric|min:0',
            'vuelto_entregado' => 'required|numeric|min:0',
            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'required|distinct|exists:productos,id',
            'arraycantidad' => 'required|array|min:1',
            'arraycantidad.*' => 'required|integer|min:1',
            'arrayprecioventa' => 'required|array|min:1',
            'arrayprecioventa.*' => 'required|numeric|min:0',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $productoIds = $this->input('arrayidproducto', []);
                $cantidades = $this->input('arraycantidad', []);
                $precios = $this->input('arrayprecioventa', []);
                $metodoPago = $this->input('metodo_pago');
                $clienteId = $this->input('cliente_id');
                $montoRecibido = (float) $this->input('monto_recibido', 0);
                $total = round((float) $this->input('total', 0), 2);
                $subtotal = round((float) $this->input('subtotal', 0), 2);
                $lineCount = count($productoIds);

                if ($lineCount !== count($cantidades) || $lineCount !== count($precios)) {
                    $validator->errors()->add('arrayidproducto', 'El detalle de la venta está incompleto o desalineado.');
                }

                $calculatedTotal = 0.0;
                foreach ($productoIds as $index => $_) {
                    $cantidad = (float) ($cantidades[$index] ?? 0);
                    $precio = (float) ($precios[$index] ?? 0);
                    $calculatedTotal += $cantidad * $precio;
                }

                $calculatedTotal = round($calculatedTotal, 2);

                if (abs($calculatedTotal - $subtotal) > 0.01 || abs($calculatedTotal - $total) > 0.01) {
                    $validator->errors()->add('total', 'El total enviado no coincide con el detalle de productos.');
                }

                if ($metodoPago === MetodoPagoEnum::Fiado->value) {
                    if ($montoRecibido >= $total) {
                        $validator->errors()->add('monto_recibido', 'Una venta fiada debe dejar saldo pendiente. Usa otro método si ya fue pagada.');
                    }

                    $cliente = $clienteId ? Cliente::find($clienteId) : null;
                    if (!$cliente || !$cliente->isFiado()) {
                        $validator->errors()->add('cliente_id', 'Las ventas fiadas solo se permiten para clientes de crédito.');
                    }
                } elseif ($montoRecibido < $total) {
                    $validator->errors()->add('monto_recibido', 'El monto recibido no puede ser menor al total de la venta.');
                }
            },
        ];
    }
}
