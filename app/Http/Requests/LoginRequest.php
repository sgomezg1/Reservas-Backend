<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'email' => 'required|email|min:4',
            'password' => 'required|string'
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
            'password.required' => 'La contraseña es obligatoria',
            'password.string' => 'Las contraseñas son de tipo caracter',
            'email.email' => 'Los correos deben ser de tipo entero',
            'email.required' => 'Debe agregar minimo un correo',
            'email.string' => 'Los correos deben ser de tipo entero'
        ];
    }
}
