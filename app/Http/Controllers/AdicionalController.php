<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Adicional;
use App\Http\Requests\AdicionalRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
		$adicional = Adicional::where("estado_adicional", true)->get();
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
	        $adicional = new Adicional();
			$adicional->nom_adicional = $request->nom_adicional;
			$adicional->cant_adicional = $request->cant_adicional;
            $adicional->precio_adicional = $request->precio_adicional;
            $adicional->estado_adicional = 1;
            $insertar = $adicional->save();
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
        $adicional = Adicional::findOrFail($id);
        return response()->json([
            "success" => true,
            "adicional" => $adicional
        ]);
    }

    public function update(Request $request, $id){
		$adicional = Adicional::findOrFail($id);
        $adicional->nom_adicional = $request->nom_adicional;
        $adicional->cant_adicional = $request->cant_adicional;
        $adicional->precio_adicional = $request->precio_adicional;
        $insertar = $adicional->save();
        if(!$insertar){
            return response()->json([
                "success" => false,
                "mensaje" => "Error al actualizar adicional, verifique por favor los datos ingresados"
            ]);
        }
        return response()->json([
            "success" => true,
            "mensaje" => "Adicional actualizado exitosamente"
        ]);

    }

    public function delete($id){
		$adicional = Adicional::findOrFail($id);
        if(!$adicional->delete()){
            return response()->json([
                "success" => false,
                "mensaje" => "Error al eliminar adicional"
            ]);
        }
        return response()->json([
            "success" => true,
            "mensaje" => "Adicional eliminado con exito"
        ]);
    }

    public function control_state($id){
        $adicional = Adicional::findOrFail($id);
        $adicional->estado_adicional = !$adicional->estado_adicional;
        $adicional->save();
        $mensaje = "Adicional inhabilitado correctamente";
        if(!$adicional->estado_adicional){
            $mensaje = "El Adicional esta habilitado ahora mismo";
        }
		return response()->json([
            "success" => true,
            "mensaje" => $mensaje
        ]);
    }
}
