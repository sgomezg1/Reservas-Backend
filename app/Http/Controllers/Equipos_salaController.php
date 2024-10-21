<?php

namespace App\Http\Controllers;

use App\Equipos_sala;
use App\Multa;
use App\Sala;
use App\Http\Requests\Equipos_salaRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Equipos_salaController extends Controller
{
    protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(response()->json($validator->errors(), 422));
	}
    // Metodo para traer todas las Equipos_sala en formato JSON para el datatable del administrador

	public function read(){
		$Equipos_sala_reenviar = DB::table("equipos_sala")
			->join("sala", "equipos_sala.sala_pertenece", "sala.id_sala")
			->select("sala.nom_sala", "equipos_sala.*", "sala.id_sala")
			->get();
		return response()->json($Equipos_sala_reenviar);
	}

    public function create(Equipos_salaRequest $request){
        if (isset($request->validator) && $request->validator->fails()) {
	        return response()->json([
				'error_code'=> 'VALIDATION_ERROR', 
				'message'   => 'The given data was invalid.', 
				'errors'    => $request->validator->errors()
			]);
	    } else {
			$Equipos_sala = new Equipos_sala();
			$Equipos_sala->sala_pertenece = $request->sala_pertenece;
			$Equipos_sala->estado_sala = 1;
			$Equipos_sala->nom_equipo = $request->nom_equipo;
			if ($request->hasFile('img_equipo')) {
				$image = $request->file('img_equipo');
				$name = time().'.'.$image->getClientOriginalExtension();
				$destinationPath = public_path('/images');
				$image->move($destinationPath, $name);
				$Equipos_sala->img_equipo = $name;
				$insertar = $Equipos_sala->save();
				if($insertar){
					$success = true;
					$mensaje = "Equipo creado exitosamente";
					//$token = $this->login($request);
				} else {
					$success = false;
					$mensaje = "Error al crear equipo, intentelo mas tarde";
				}
			} else {
				$success = false;
				$mensaje = "Error, debes subir una imagen para crear un equipo";
			}
	    }
        
        return response()->json(['success' => $success, 'mensaje' => $mensaje]);
    }

    public function edit($id){
        try {
		    $Equipos_sala_datos = Equipos_sala::findOrFail($id);
            return response()->json(["success" => true, "datos_Equipos_sala" => $Equipos_sala_datos]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una Equipos_sala con este ID"]);
		}
    }

    public function update(Request $request, $id){
		try {
			$Equipos_sala = Equipos_sala::findOrFail($id);
			if($request->hasFile("img_equipo")){
				$image = $request->file('img_equipo');
				$name = time().'.'.$image->getClientOriginalExtension();
				$destinationPath = public_path('/images');
				$image->move($destinationPath, $name);
				$Equipos_sala->img_equipo = $name;
			} else {
				$Equipos_sala->img_equipo = $Equipos_sala->img_equipo;
			}
			$Equipos_sala->sala_pertenece = $request->sala_pertenece;
			$Equipos_sala->nom_equipo = $request->nom_equipo;
			$insertar = $Equipos_sala->save();
			if($insertar){
				$success = true;
				$mensaje = "Sala actualizada exitosamente";
			} else {
				$success = false;
				$mensaje = "Error al actualizar sala, verifique por favor los datos ingresados";
			}
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una noticia con este ID"]);
		}        
    }

    public function delete($id){
		try{
			$Equipos_sala = Equipos_sala::findOrFail($id);
			if($Equipos_sala->delete()){
				$success = true;
				$mensaje = "Equipos_sala eliminada con exito";
			} else {
				$success = false;
				$mensaje = "Error al eliminar recurso Equipos_sala  ";
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una Equipos_sala con este ID"]);
		}
    }

    public function control_state($id){
        $Equipos_sala = Equipos_sala::findOrFail($id);
        if($Equipos_sala->estado_sala == 1){
            $Equipos_sala->estado_sala = 0;
            $mensaje = "Equipo inhabilitado correctamente";
        } else {
            $Equipos_sala->estado_sala = 1;
            $mensaje = "El equipo ha sido habilitado ahora mismo";
        }
        $Equipos_sala->save();
		return response()->json(["success" => true, "mensaje" => $mensaje]);
    }
}
