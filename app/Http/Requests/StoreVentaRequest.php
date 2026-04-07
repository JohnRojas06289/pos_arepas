<?php

namespace App\Http\Requests;

use App\Enums\MetodoPagoEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'cliente_id' => 'nullable|exists:clientes,id',
            'comprobante_id' => 'required|exists:comprobantes,id',
            'metodo_pago' => [
                'required',
                new Enum(MetodoPagoEnum::class),
                Rule::notIn([MetodoPagoEnum::Fiado->value]),
            ],
            'subtotal' => 'required|numeric|min:0.01',
            'total' => 'required|numeric|min:0.01',
            'monto_recibido' => 'required|numeric|min:0.01',
            'vuelto_entregado' => 'required|numeric|min:0',
            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'required|distinct|exists:productos,id',
            'arraycantidad' => 'required|array|min:1',
            'arraycantidad.*' => 'required|integer|min:1',
            'arrayprecioventa' => 'required|array|min:1',
            'arrayprecioventa.*' => 'required|numeric|min:0.01',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $productoIds = $this->input('arrayidproducto', []);
                $cantidades = $this->input('arraycantidad', []);
                $precios = $this->input('arrayprecioventa', []);
                $montoRecibido = round((float) $this->input('monto_recibido', 0), 2);
                $total = round((float) $this->input('total', 0), 2);
                $subtotal = round((float) $this->input('subtotal', 0), 2);
                $lineCount = count($productoIds);

                if ($lineCount !== count($cantidades) || $lineCount !== count($precios)) {
                    $validator->errors()->add('arrayidproducto', 'El detalle de la venta está incompleto.');
                    return;
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

                if ($this->input('metodo_pago') === MetodoPagoEnum::Fiado->value) {
                    $validator->errors()->add('metodo_pago', 'La opción de fiado ya no está disponible en el punto de venta.');
                }

                if ($montoRecibido < $total) {
                    $validator->errors()->add('monto_recibido', 'El monto recibido no puede ser menor al total de la venta.');
                }
            },
        ];
    }
}
