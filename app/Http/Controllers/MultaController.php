<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Multa;
use App\Http\Requests\MultaRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Mail;
use App\Mail\MultaMail;
use Illuminate\Http\JsonResponse;

class MultaController extends Controller
{
    protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(response()->json($validator->errors(), 422));
	}
    // Metodo para traer todas las Multa en formato JSON para el datatable del administrador

	public function read(){
		$Multa_reenviar = DB::table("multa")
			->join("reserva", "multa.reserva_id", "reserva.id_reserva")
			->join("usuario", "reserva.id_usuario_reserva", "usuario.id_usuario")
			->join("sala", "sala.id_sala", "reserva.sala_reserva")
			->select("multa.*", "reserva.fecha_reserva", "reserva.hora_reserva", "usuario.nom_banda", "usuario.email", "sala.nom_sala")
			->get();
		return response()->json($Multa_reenviar);
	}

	// Metodo para validar si un usuario tiene multas para no permitir reservar

	public function validar_usuario_con_multa($id) {
		$Multa_reenviar = DB::table("multa")
			->select("multa.usuario_multa")
			->where("multa.usuario_multa", "=", $id)
			->get();
		return response()->json($Multa_reenviar);
	}

	// Metodo para traer todas las multas por ID de usuario

	public function multas_por_usuario($id) {
		$Multa_reenviar = DB::table("multa")
			->join("reserva", "multa.reserva_id", "reserva.id_reserva")
			->join("usuario", "reserva.id_usuario_reserva", "usuario.id_usuario")
			->join("sala", "sala.id_sala", "reserva.sala_reserva")
			->select("multa.*", "reserva.id_reserva", "reserva.fecha_reserva", "reserva.hora_reserva", "usuario.nom_banda", "usuario.email", "sala.nom_sala")
			->where('reserva.id_usuario_reserva', '=', $id)
			->where('multa.estado_multa', '=', "1")
			->orderBy('reserva.fecha_reserva', 'desc')
			->orderBy('reserva.hora_reserva', 'desc')
			->get();
		return response()->json($Multa_reenviar);
	}

    public function create($array){
    /* public function create(Request $request){ */
        /*if (isset($request->validator) && $request->validator->fails()) {
	        return response()->json([
				'error_code'=> 'VALIDATION_ERROR', 
				'message'   => 'The given data was invalid.', 
				'errors'    => $request->validator->errors()
			]);
		} else {*/
	        $Multa = new Multa();
            $Multa->estado_multa = 1;
			$Multa->reserva_id = $array['reserva_id'];
			$Multa->total_multa = $array['total_multa'];
            $Multa->usuario_multa = $array['usuario_multa'];
			$Multa->updated_at = date("Y-m-d H:i:s");
			$Multa->created_at = date("Y-m-d H:i:s");
			$insertar = $Multa->save();
            if($insertar){
				$reserva = new ReservaController();
				$datos_reserva = $reserva->read_full_reserva($array['reserva_id']);
				$datos_reserva_get = $datos_reserva->getData();
				Mail::to($datos_reserva_get->email)->send(new MultaMail($datos_reserva_get));
                $success = true;
                $mensaje = "Multa creada exitosamente";
                //$token = $this->login($request);
            } else {
                $success = false;
                $mensaje = "Error al crear Multa, intentelo mas tarde";
            }
	    //}
        
        return response()->json(['success' => $success, 'mensaje' => $mensaje]);
    }

    public function edit($id){
        try {
		    $Multa_datos = Multa::findOrFail($id);
            return response()->json(["success" => true, "datos_Multa" => $Multa_datos]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una Multa con este ID"]);
		}
    }

	// Metodo para validar si un usuario tiene multa

	public function validar_multa_usuario($id){
		$validar_multa_usuario = DB::table('reserva')
			->join("multa", "reserva.id_reserva", "multa.reserva_id")
			->select("reserva.id_reserva")
			->where("multa.usuario_multa", "=", $id)
			->where("multa.estado_multa", "=", 1)
			->get();
		if($validar_multa_usuario->isNotEmpty()){
			return false;
		} else {
			return true;
		}
	}

    public function update(Request $request, $id){
		try {
			$Multa = Multa::findOrFail($id);
			$Multa->sala_Multa = $request->sala_Multa;
			$Multa->fecha_Multa = $request->fecha_Multa;
            $Multa->hora_Multa = $request->hora_Multa;
			$insertar = $Multa->save();
			if($insertar){
				$success = true;
				$mensaje = "Multa actualizada exitosamente";
			} else {
				$success = false;
				$mensaje = "Error al actualizar Multa, verifique por favor los datos ingresados";
			}
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una Multa con este ID"]);
		}
        
    }

    public function delete($id){
		try{
			$Multa = Multa::findOrFail($id);
			if($Multa->delete()){
				$success = true;
				$mensaje = "Multa eliminada con exito";
			} else {
				$success = false;
				$mensaje = "Error al eliminar Multa ";
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una Multa con este ID"]);
		}
    }

    public function control_state($id){
        $Multa = Multa::findOrFail($id);
        if($Multa->estado_multa == 1){
            $Multa->estado_multa = 0;
            $mensaje = "Multa inhabilitada correctamente";
        } else {
            $Multa->estado_multa = 1;
            $mensaje = "La Multa esta habilitada ahora mismo";
        }
        $Multa->save();
		return response()->json(["success" => true, "mensaje" => $mensaje]);
    }
}
