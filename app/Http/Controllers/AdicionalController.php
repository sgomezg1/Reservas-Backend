<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Adicional;
use App\Http\Requests\AdicionalRequest;
use Illuminate\Support\Facades\DB;

class AdicionalController extends Controller
{
    protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(response()->json($validator->errors(), 422));
	}
    // Metodo para traer todas las Adicional en formato JSON para el datatable del administrador

	public function read(){
		$Adicional = new Adicional();
		$Adicional_reenviar = $Adicional::all();
		return response()->json($Adicional_reenviar);
	}

	public function adicionales_para_reserva($fecha, $hora) {
		$array_adicionales = array();
		$array_reservados = array();
		$adicional = DB::table("adicional")
			->select("*")
			->where("estado_adicional", "=", 1)
			->get();
		$reservas_adicional = DB::table("reserva_adicional")
			->select("adicional_id", DB::raw('count(adicional_id) as total'))
			->where("fecha_reserva", "=", $fecha)
			->where("hora_reserva", "=", $hora)
			->groupBy('adicional_id')
			->get();
		if(sizeof($reservas_adicional) > 0) {
			foreach($adicional as $ad) {
				$array_agregar = array(
					'id_adicional' => $ad->id_adicional,
					'cant_adicional' => $ad->cant_adicional,
					'nom_adicional' => $ad->nom_adicional,
					'precio_adicional' => $ad->precio_adicional
				);		
				array_push($array_adicionales, $array_agregar);		
				foreach($reservas_adicional as $res) {
					$array_agregar = array(
						'adicional_id' => $res->adicional_id,
						'total' => $res->total 
					);
					array_push($array_reservados, $array_agregar);				
				}
			}

			foreach($array_adicionales as $key => $add) {
				foreach($array_reservados as $key2 => $reser) {
					if(  ($add['id_adicional'] == $reser['adicional_id']) && ($add['cant_adicional'] == $reser['total']) ) {
						unset($array_adicionales[$key]);
						break;
					}
				}
			}
			return response()->json(array_values($array_adicionales));
		} else {
			return response()->json($adicional);
		}
	}

    public function create(AdicionalRequest $request){
        if (isset($request->validator) && $request->validator->fails()) {
	        return response()->json([
				'error_code'=> 'VALIDATION_ERROR', 
				'message'   => 'The given data was invalid.', 
				'errors'    => $request->validator->errors()
			]);
	    } else {
	        $Adicional = new Adicional();
			$Adicional->nom_adicional = $request->nom_adicional;
			$Adicional->cant_adicional = $request->cant_adicional;
            $Adicional->precio_adicional = $request->precio_adicional;
            $Adicional->estado_adicional = 1;
            $insertar = $Adicional->save();
            if($insertar){
                $success = true;
                $mensaje = "Adicional creado exitosamente";
                //$token = $this->login($request);
            } else {
                $success = false;
                $mensaje = "Error al crear adicional, intentelo mas tarde";
            }
	    }
        
        return response()->json(['success' => $success, 'mensaje' => $mensaje]);
    }

    public function edit($id){
        try {
		    $Adicional_datos = Adicional::findOrFail($id);
            return response()->json(["success" => true, "datos_adicional" => $Adicional_datos]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un adicional con este ID"]);
		}
    }

    public function update(Request $request, $id){
		try {
			$Adicional = Adicional::findOrFail($id);
			$Adicional->nom_adicional = $request->nom_adicional;
			$Adicional->cant_adicional = $request->cant_adicional;
            $Adicional->precio_adicional = $request->precio_adicional;
			$insertar = $Adicional->save();
			if($insertar){
				$success = true;
				$mensaje = "Adicional actualizado exitosamente";
			} else {
				$success = false;
				$mensaje = "Error al actualizar adicional, verifique por favor los datos ingresados";
			}
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un adicional con este ID"]);
		}
        
    }

    public function delete($id){
		try{
			$Adicional = Adicional::findOrFail($id);
			if($Adicional->delete()){
				$success = true;
				$mensaje = "Adicional eliminado con exito";
			} else {
				$success = false;
				$mensaje = "Error al eliminar adicional  ";
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una Adicional con este ID"]);
		}
    }

    public function control_state($id){
        $Adicional = Adicional::findOrFail($id);
        if($Adicional->estado_adicional == 1){
            $Adicional->estado_adicional = 0;
            $mensaje = "Adicional inhabilitado correctamente";
        } else {
            $Adicional->estado_adicional = 1;
            $mensaje = "El Adicional esta habilitado ahora mismo";
        }
        $Adicional->save();
		return response()->json(["success" => true, "mensaje" => $mensaje]);
    }
}
