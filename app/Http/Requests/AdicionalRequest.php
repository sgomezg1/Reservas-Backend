<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdicionalRequest extends FormRequest
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
            'nom_adicional' => 'required|max:50',
            'cant_adicional' => 'required|max:3',
            'precio_adicional' => 'required|integer|digits_between:1,11'
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
            'nom_adicional.required' => 'Nombre de adicional requerido',
            'nom_adicional.max' => 'El adicional debe tener maximo 50 caracteres',
            'cant_adicional.required' => 'La cantidad es requerida',
            'cant_adicional.max' => 'Maximo un numero de 3 cifras',
            'precio_adicional.required' => 'El precio es requerido',
            'precio_adicional.digits_between' => 'El precio debe tener entre 1 y 11 digitos',
            'precio_adicional.integer' => 'El precio debe ser un numero'
        ];
    }
}
