<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalaRequest extends FormRequest
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
    public function rules()
    {
        return [
            'nom_sala' => 'min:3',
            'precio_sala' => 'min:1|integer',
            'foto_sala' => 'max:2048'
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
            'nom_sala.min' => 'Minimo 3 caracteres en el nombre de la sala',
            'foto_sala.max' => 'Maximo puedes subir una imagen de 2 MB',
            'precio_sala.min' => 'Minimo el precio debe tener 1 caracter',
            'precio_sala.integer' => 'El precio del bloque solo puede contener numeros enteros'
        ];
    }
}
