<?php

namespace App\Http\Controllers;

use App\Enums\TokenAbility;
use App\Models\User;
use App\Models\CorreosUsuario;
use App\Http\Requests\UsuarioRequest;
use App\Http\Requests\LoginRequest;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(response()->json($validator->errors(), 422));
	}
    public function login(LoginRequest $request)
    {
        $authAttempt = Auth::attempt($request->only(['email', 'password']));
        if (!$authAttempt) {
            return response()->json(['error' => 'Error, credenciales incorrectas'], 401);
        }
        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('access_token', [$user->rol->nom_rol])->plainTextToken;
        return $this->respondWithToken($token);
    }

    public function signup(UsuarioRequest $request){
        if (isset($request->validator) && $request->validator->fails()) {
	        return response()->json([
				'error_code'=> 'VALIDATION_ERROR',
				'message'   => 'Los datos recibidos no son validos.',
				'errors'    => $request->validator->errors()
			], 422);
	    }
        if (User::where("email", $request->email)->first()) {
            return response()->json([
                'success' => false,
                'message' => "Error al crear usuario, Ya hay un usuario registrado con este correo"
            ], 400);
        }
        $insertarUsuario = User::create([
            "username" => $request->email,
            "nom_banda" => $request->nom_banda,
            "nom_registra" => $request->nom_registra,
            "ape_registra" => $request->ape_registra,
            "telefono_usuario" => $request->telefono_usuario,
            "estado_usuario" => 1,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "updated_at" => date("Y-m-d H:i:s"),
            "created_at" => date("Y-m-d H:i:s"),
            "rol_usuario" => $request->rol_usuario
        ]);
        if(!$insertarUsuario){
            return response()->json([
                'success' => false,
                'message' => "Error al crear usuario, intentelo mas tarde"
            ], 400);
        }
        if(sizeof($request->email_adicional)>0) {
            foreach($request->email_adicional as $val) {
                CorreosUsuario::create(['dir_correo' => $val["nombre_correo"], 'id_usuario_pert' => $insertarUsuario->id]);
                // Mail::to($val["nombre_correo"])->send(new BienvenidoMail($Usuario));
            }
        }
        // Mail::to($request->email)->send(new BienvenidoMail($Usuario));
        return response()->json([
            'success' => true,
            'message' => "Usuario creado exitosamente, ya puedes realizar reservas en nuestra sala de ensayo"
        ], 200);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json([
            "success" => true,
            "user" => auth()->user()->select(
                'nom_registra',
                'ape_registra',
                'email',
                'nom_banda'
            )->first()
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            "success" => true,
            "message"=>"Sesion finalizada"
        ]);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer'
        ], 200);
    }
}
