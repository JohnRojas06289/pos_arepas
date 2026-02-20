<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductoRequest extends FormRequest
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
        $producto = $this->route('producto');
        return [
            'codigo' => 'nullable|max:50', // Removed unique
            'nombre' => 'required|max:255', // Removed unique
            'descripcion' => 'nullable|max:255',
            'img_path' => 'nullable|image|mimes:png,jpg,jpeg,webp,avif,gif,svg,bmp',
            'marca_id' => 'nullable', 
            'presentacione_id' => 'nullable', 
            'categoria_id' => 'nullable',
            'color' => 'nullable|string|max:100',
            'material' => 'nullable|string|max:100',
            'genero' => 'nullable|in:Hombre,Mujer,Unisex',
            'precio' => 'nullable|numeric|min:0'
        ];
    }

    public function attributes()
    {
        return [
            'marca_id' => 'marca',
            'presentacione_id' => 'presentación',
            'categoria_id' => 'categoria'
        ];
    }

    public function messages()
    {
        return [
            //'codigo.required' => 'Se necesita un campo código'
        ];
    }
}
