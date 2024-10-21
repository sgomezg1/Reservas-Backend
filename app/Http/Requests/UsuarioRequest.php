<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioRequest extends FormRequest
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
            'nom_registra' => 'required|min:4',
            'email' => 'required|email|min:4',
            'ape_registra' => 'required|min:4',
            'password' => 'required|string',
            'nom_banda' => 'required|string'
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
            'nom_banda.required' => 'El nombre de la banda es requerido',
            'nom_banda.min' => 'El nombre de la banda minimo debe tener 4 caracteres',
            'nom_registra.required' => 'El nombre del integrante de la banda es requerido',
            'nom_registra.min' => 'El nombre del integrante de la banda minimo debe tener 4 caracteres',
            'ape_registra.required' => 'El apellido del integrante de la banda es requerido',
            'ape_registra.min' => 'El apellido del integrante de debe tener 4 caracteres',
            'password.required' => 'La contraseña es obligatoria',
            'password.string' => 'Las contraseñas son de tipo caracter',
            'email.email' => 'Los correos deben ser de tipo entero',
            'email.required' => 'Debe agregar minimo un correo',
            'email.string' => 'Los correos deben ser de tipo entero'
        ];
    }
}
