<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Reserva_adicionalRequest extends FormRequest
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
            'reserva_id' => 'required|integer',
            'adicional_id' => 'required|integer',
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
            'resresva_id.required' => 'ID de reserva requerido',
            'resresva_id.integer' => 'ID de reserva debe ser de tipo numerico',
            'adicional_id.required' => 'Adicional de la sala requerido',
            'adicional_id.integer' => 'el ID del adicional debe ser de tipo numerico'
        ];
    }
}
