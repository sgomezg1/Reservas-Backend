<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Mail\BienvenidoMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\UsuarioRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Mail\Mailable;
use App\Mail\RecuperarPassMail;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(response()->json($validator->errors(), 422));
	}
    	
	// Metodo para enviar correo de recuperacion de contraseña a un usuario

	public function enviar_correo_solicitud_reestablecer_contrasenia($email) {
		if(!$this->validar_correo_existente($email)) {
			return response()->json(['data' => "Este email no se encuentra registrado en nuestro sistema", "error" => 1]);
		}
		$token = $this->crear_token($email);
		Mail::to($email)->send(new RecuperarPassMail($email, $token));
		return response()->json(['data' => "Se ha enviado un correo para que recuperes la contraseña", "error" => 0]);
	}

	// Metodo para validar si un correo existe

	public static function validar_correo_existente($email) {
		return User::where('email', $email)->first();    
	}

	// Metodo para validar si un usuario existe

	public static function validar_usu_existente($username) {
		return User::where('username', $username)->first();    
	}

	// Metodo para crear token de recuperacion de contraseña

	public function crear_token($email) {
		$token_antiguo = DB::table('password_resets')->select("token")->where('email', "=", $email)->first();
		if($token_antiguo) {
			return $token_antiguo->token;
		}
		$token = str_random(60);
		$this->guardar_token_base_datos($email, $token);
		return $token;
	}

	// Metodo para guardar token en base de datos

	public function guardar_token_base_datos($email, $token) {
		DB::table('password_resets')->insert([
			'email' => $email,
			'token' => $token,
			'created_at' => date("Y-m-d H:i:s")
		]);
	}

	// Metodo para procesar el token recibido en el correo de recuperar contraseña

	public function procesar_token_de_respuesta(Request $request) {
		return $this->obtener_token_por_email($request) > 0 ? $this->cambiar_contrasenia($request) : $this->no_existe_correo();
	}

	// Metodo para obtener el token de base de datos junto al token

	public function obtener_token_por_email($request) {
		return DB::table('password_resets')->where(['email'=> $request->email, 'token' => $request->token])->count();
	}

	// Metodo para cambiar la contraseña del usuario

	public function cambiar_contrasenia($request) {
		$usuario = User::where("email", $request->email)->first();
		echo $request->password;
		$usuario->password = $request->password;
		if($usuario->save()) {
			$success = true;
			$respuesta = 'Contraseña actualizada correctamente';
		}	
		// DB::table('password_resets')->where('email', $request->email)->delete();
		return response()->json(['success'=>$success, 'data' => $respuesta]);
	}

	// Metodo para responder que no existe el correo

	public function no_existe_correo() {
		return response()->json(['error' => 'El token o el correo son incorrectos']);
	}
}
