<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Mail\BienvenidoMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\UsuarioRequest;
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

    public function read() {
        return response()->json([
            'success' => true,
            'user' => User::select(
                'id',
                'nom_registra',
                'ape_registra',
                'email',
                'username',
                'nom_banda',
                'telefono_usuario',
                'rol_usuario',
                'estado_usuario'
            )->get()
        ]);
    }

    public function edit($id) {
        $user = User::select(
            'id',
            'nom_registra',
            'ape_registra',
            'email',
            'username',
            'nom_banda',
            'telefono_usuario',
            'rol_usuario',
            'estado_usuario'
        )->where('id', $id)->firstOrFail();
        return response()->json([
            "success" => true,
            "user" => $user
        ]);
    }

    public function update(Request $request, $id) {
        $user = User::findOrFail($id);
        $user->nom_registra = $request->nom_registra;
        $user->ape_registra = $request->ape_registra;
        $user->telefono_usuario = $request->telefono_usuario;
        $user->nom_banda = $request->nom_banda;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->updated_at = date("Y-m-d H:i:s");
        $insertar = $user->save();
        if(!$insertar){
            return response()->json([
                "success" => false,
                "mensaje" => "Error al actualizar usuario, verifique por favor los datos ingresados"
            ]);
        }
        return response()->json([
            "success" => true,
            "mensaje" => "Usuario actualizado exitosamente"
        ]);
    }

    public function control_state($id) {
        $user = User::findOrFail($id);
        $user->estado_usuario = !$user->estado_usuario;
        $user->save();
		if (!$user->estado_usuario) {
            return response()->json([
                "success" => true,
                "mensaje" => "Usuario desactivado correctamente"
            ]);
		}
		return response()->json([
            "success" => true,
            "mensaje" => "Usuario activado correctamente"
        ]);
    }

    public function delete($id) {
        $user = User::findOrFail($id);
        if(!$user->delete()){
            return response()->json([
                'success' => false,
                'mensaje' => "Error al eliminar usuario"
            ]);
        }
        return response()->json([
            'success' => true,
            'mensaje' => "Usuario eliminada con exito"
        ]);
    }

	// Metodo para enviar correo de recuperacion de contraseña a un usuario

	public function enviar_correo_solicitud_reestablecer_contrasenia($email) {
		if(!$this->validar_correo_existente($email)) {
			return response()->json([
                'success' => false,
                'data' => "Este email no se encuentra registrado en nuestro sistema",
            ]);
		}
		$token = $this->crear_token($email);
		// Mail::to($email)->send(new RecuperarPassMail($email, $token));
		return response()->json([
            'success' => true,
            'data' => "Se ha enviado un correo para que recuperes la contraseña"
        ]);
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
		$token_antiguo = DB::table('password_resets')->select("token")->where('email', $email)->first();
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
