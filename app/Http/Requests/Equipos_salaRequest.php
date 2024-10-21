<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Equipos_salaRequest extends FormRequest
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
            'sala_pertenece' => 'required|integer',
            'img_equipo' => 'required',
            'nom_equipo' => 'required',
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
            'sala_pertenece.required' => 'Sala a la que pertenece es requerida',
            'sala_pertenece.integer' => 'El ID de la sala debe ser de tipo numerico',
            'img_equipo.required' => 'Minimo 5 caracteres en el nombre de usuario',
            'nom_equipo.required' => 'El nombre del equipo es requerido',
        ];
    }
}
