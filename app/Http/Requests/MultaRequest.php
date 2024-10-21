<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MultaRequest extends FormRequest
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
            'total_multa' => 'required|integer',
            'estado_multa' => 'required'
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
            'reserva_id.required' => 'ID de reserva es obligatorio',
            'reserva_id.integer' => 'ID de reserva debe ser de tipo numerico',
            'total_multa.required' => 'Total de la multa requerido',
            'total_multa.integer' => 'Total multa debe ser de tipo numerico',
            'estado_multa.required' => 'Estado de la multa debe ser obligatorio'
        ];
    }
}
