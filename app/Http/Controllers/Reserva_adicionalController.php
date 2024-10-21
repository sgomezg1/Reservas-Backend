<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reserva_adicional;
use Illuminate\Support\Facades\DB;

class Reserva_adicionalController extends Controller
{
    protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(response()->json($validator->errors(), 422));
	}
    // Metodo para traer todas las Reserva_adicional en formato JSON para el datatable del administrador

	public function read(){
		$Reserva_adicional = new Reserva_adicional();
		$Reserva_adicional_reenviar = $Reserva_adicional::all();
		return response()->json($Reserva_adicional_reenviar);
	}

    public function create($adicional_id, $reserva_id, $fecha_reserva, $hora_reserva){
        $Reserva_adicional = new Reserva_adicional();
		$Reserva_adicional->reserva_id = $reserva_id;
		$Reserva_adicional->adicional_id = $adicional_id;
		$Reserva_adicional->fecha_reserva = $fecha_reserva;
		$Reserva_adicional->hora_reserva = $hora_reserva;
		$Reserva_adicional->updated_at = date("Y-m-d H:i:s");
		$Reserva_adicional->created_at = date("Y-m-d H:i:s");
		$insertar = $Reserva_adicional->save();
		if($insertar){
			$success = true;
			$mensaje = "Reserva_adicional creada exitosamente";
			//$token = $this->login($request);
		} else {
			$success = false;
			$mensaje = "Error al crear Reserva_adicional, intentelo mas tarde";
		}
        
        return response()->json(['success' => $success, 'mensaje' => $mensaje]);
    }

	public function datos_reserva_adicional_por_id_reserva($id_reserva) {
		$reserva_adicional_por_id_reserva = DB::table('reserva_adicional')
			->join('adicional', 'reserva_adicional.adicional_id', 'adicional.id_adicional')
			->select('adicional.nom_adicional', 'adicional.precio_adicional')
			->where('reserva_adicional.reserva_id', '=', $id_reserva)
			->get();
		return response()->json(['success' => true, 'data' => $reserva_adicional_por_id_reserva]);
	}

    public function edit($id){
        try {
		    $Reserva_adicional_datos = Reserva_adicional::findOrFail($id);
            return response()->json(["success" => true, "datos_Reserva_adicional" => $Reserva_adicional_datos]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una Reserva_adicional con este ID"]);
		}
    }

    public function update(Request $request, $id){
		try {
			$Reserva_adicional = Reserva_adicional::findOrFail($id);
			$Reserva_adicional->reserva_id = $request->reserva_id;
			$Reserva_adicional->adicional_id = $request->adicional_id;
			$insertar = $Reserva_adicional->save();
			if($insertar){
				$success = true;
				$mensaje = "Reserva_adicional actualizada exitosamente";
			} else {
				$success = false;
				$mensaje = "Error al actualizar Reserva_adicional, verifique por favor los datos ingresados";
			}
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una Reserva_adicional con este ID"]);
		}
        
    }

    public function delete($id){
		try{
			$Reserva_adicional = Reserva_adicional::findOrFail($id);
			if($Reserva_adicional->delete()){
				$success = true;
				$mensaje = "Reserva_adicional eliminada con exito";
			} else {
				$success = false;
				$mensaje = "Error al eliminar recurso Reserva_adicional  ";
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una Reserva_adicional con este ID"]);
		}
    }

    public function control_state($id){
        $Reserva_adicional = Reserva_adicional::findOrFail($id);
        if($Reserva_adicional->estado_Reserva_adicional == 1){
            $Reserva_adicional->estado_Reserva_adicional = 0;
            $mensaje = "Reserva_adicional inhabilitada correctamente";
        } else {
            $Reserva_adicional->estado_Reserva_adicional = 1;
            $mensaje = "La Reserva_adicional esta habilitada ahora mismo";
        }
        $Reserva_adicional->save();
		return response()->json(["success" => true, "mensaje" => $mensaje]);
    }
}
