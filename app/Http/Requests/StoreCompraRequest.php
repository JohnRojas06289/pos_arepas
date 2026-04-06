<?php

namespace App\Http\Requests;

use App\Enums\MetodoPagoEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class StoreCompraRequest extends FormRequest
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
            'proveedore_id' => 'required|exists:proveedores,id',
            'comprobante_id' => 'required|exists:comprobantes,id',
            'numero_comprobante' => 'max:255|nullable|unique:compras,numero_comprobante',
            'file_comprobante' => 'nullable|file|mimes:pdf|max:2048',
            'metodo_pago' => ['required', new Enum(MetodoPagoEnum::class)],
            'fecha_hora' => 'required|date|date_format:Y-m-d\TH:i',
            'subtotal' => 'required|numeric|min:0.01',
            'total' => 'required|numeric|min:0.01',
            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'required|distinct|exists:productos,id',
            'arraycantidad' => 'required|array|min:1',
            'arraycantidad.*' => 'required|integer|min:1',
            'arraypreciocompra' => 'required|array|min:1',
            'arraypreciocompra.*' => 'required|numeric|min:0.01',
            'arrayfechavencimiento' => 'nullable|array',
            'arrayfechavencimiento.*' => 'nullable|date',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $productoIds = $this->input('arrayidproducto', []);
                $cantidades = $this->input('arraycantidad', []);
                $precios = $this->input('arraypreciocompra', []);
                $fechas = $this->input('arrayfechavencimiento', []);
                $lineCount = count($productoIds);

                if (
                    $lineCount !== count($cantidades) ||
                    $lineCount !== count($precios) ||
                    ($fechas !== [] && $lineCount !== count($fechas))
                ) {
                    $validator->errors()->add('arrayidproducto', 'El detalle de la compra está incompleto o desalineado.');
                }

                $calculatedTotal = 0.0;
                foreach ($productoIds as $index => $_) {
                    $cantidad = (float) ($cantidades[$index] ?? 0);
                    $precio = (float) ($precios[$index] ?? 0);
                    $calculatedTotal += $cantidad * $precio;
                }

                $calculatedTotal = round($calculatedTotal, 2);
                $subtotal = round((float) $this->input('subtotal', 0), 2);
                $total = round((float) $this->input('total', 0), 2);

                if (abs($calculatedTotal - $subtotal) > 0.01 || abs($calculatedTotal - $total) > 0.01) {
                    $validator->errors()->add('total', 'El total enviado no coincide con el detalle de productos.');
                }
            },
        ];
    }
}
