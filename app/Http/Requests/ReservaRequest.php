<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservaRequest extends FormRequest
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
            'sala_reserva' => 'required|integer',
            'fecha_reserva' => 'required|date',
            'hora_reserva' => 'required'
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
            'sala_reserva.required' => 'Debe seleccionar una sala a reservar',
            'sala_reserva.integer' => 'El ID de la sala a reservar debe ser de tipo numerico',
            'fecha_reserva.required' => 'La fecha de reserva es obligatoria',
            'fecha_reserva.date' => 'Ingrese una fecha valida',
            'hora_reserva.required' => 'Hora de reserva es obligatoria'
        ];
    }
}
