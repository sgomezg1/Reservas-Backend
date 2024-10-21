<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DescuentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'valor_descuento' => 'required|integer',
            'tipo_descuento' => 'required|integer'
        ];
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            'valor_descuento.required' => 'Es obligatorio asignar un valor al descuento',
            'valor_descuento.integer' => 'El descuento tiene que tener solo numeros',
            'tipo_descuento.required' => 'Debe asignar un tipo de descuento, descuento un dia en la semana o descuento parametrizable',
            'tipo_descuento.integer' => 'El tipo de descuento debe ser un numero entero'
        ];
    }
}
