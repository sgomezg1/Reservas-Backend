<?php

namespace App\Http\Controllers;

use App\Models\EquiposSala;
use App\Http\Requests\EquiposSalaRequest;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class EquiposSalaController extends Controller
{
    protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(response()->json($validator->errors(), 422));
	}
    // Metodo para traer todas las EquiposSala en formato JSON para el datatable del administrador

	public function read(){
		$EquiposSala_reenviar = DB::table("EquiposSala")
			->join("sala", "EquiposSala.sala_pertenece", "sala.id_sala")
			->select("sala.nom_sala", "EquiposSala.*", "sala.id_sala")
			->get();
		return response()->json($EquiposSala_reenviar);
	}

    public function create(EquiposSalaRequest $request){
        if (isset($request->validator) && $request->validator->fails()) {
	        return response()->json([
				'error_code'=> 'VALIDATION_ERROR',
				'message'   => 'The given data was invalid.',
				'errors'    => $request->validator->errors()
			]);
	    } else {
			$EquiposSala = new EquiposSala();
			$EquiposSala->sala_pertenece = $request->sala_pertenece;
			$EquiposSala->estado_sala = 1;
			$EquiposSala->nom_equipo = $request->nom_equipo;
			if ($request->hasFile('img_equipo')) {
				$image = $request->file('img_equipo');
				$name = time().'.'.$image->getClientOriginalExtension();
				$destinationPath = public_path('/images');
				$image->move($destinationPath, $name);
				$EquiposSala->img_equipo = $name;
				$insertar = $EquiposSala->save();
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
        $data = EquiposSala::findOrFail($id);
        return response()->json(["success" => true, "equipos" => $data]);
    }

    public function update(Request $request, $id){
		$EquiposSala = EquiposSala::findOrFail($id);
        if($request->hasFile("img_equipo")){
            $image = $request->file('img_equipo');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
            $EquiposSala->img_equipo = $name;
        } else {
            $EquiposSala->img_equipo = $EquiposSala->img_equipo;
        }
        $EquiposSala->sala_pertenece = $request->sala_pertenece;
        $EquiposSala->nom_equipo = $request->nom_equipo;
        $insertar = $EquiposSala->save();
        if($insertar){
            $success = true;
            $mensaje = "Sala actualizada exitosamente";
        } else {
            $success = false;
            $mensaje = "Error al actualizar sala, verifique por favor los datos ingresados";
        }
        return response()->json(["success" => $success, "mensaje" => $mensaje]);
    }

    public function delete($id){
		$EquiposSala = EquiposSala::findOrFail($id);
        if($EquiposSala->delete()){
            $success = true;
            $mensaje = "Equipo de sala eliminada con exito";
        } else {
            $success = false;
            $mensaje = "Error al eliminar equipo de la sala ";
        }
        return response()->json(["success" => $success, "mensaje" => $mensaje]);
    }

    public function control_state($id){
        $EquiposSala = EquiposSala::findOrFail($id);
        if($EquiposSala->estado_sala == 1){
            $EquiposSala->estado_sala = 0;
            $mensaje = "Equipo inhabilitado correctamente";
        } else {
            $EquiposSala->estado_sala = 1;
            $mensaje = "El equipo ha sido habilitado ahora mismo";
        }
        $EquiposSala->save();
		return response()->json(["success" => true, "mensaje" => $mensaje]);
    }
}
