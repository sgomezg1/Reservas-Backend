<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;
use App\Descuento;
use App\Http\Requests\DescuentoRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DescuentoController extends Controller
{
    protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
    
    public function read(){
		$Descuento = new Descuento();
		$Descuento_reenviar = $Descuento::all();
		return response()->json($Descuento_reenviar);
    }
    
    // Metodo para obtener descuentos por dia

    public function read_descuentos_por_dia($fecha) {
        $descuento_por_dia = DB::table("descuentos")
        ->select("*")
        ->where("fecha_descuento", "=", $fecha)
        ->get();
        return response()->json($descuento_por_dia);
	}
	
	// Metodo para validar descuentos antes de hacer una reserva

	public function read_descuentos_reserva($dia) {
		$nameOfDay = date('D', strtotime($dia));
		$array_dias = array(
			0 => 'Sun',
			1 => 'Mon',
			2 => 'Tue',
			3 => 'Wed',
			4 => 'Thu',
			5 => 'Fri',
			6 => 'Sat',
		);
		$dia_num = array_search($nameOfDay, $array_dias);
		return DB::table("descuentos")
        ->select("dia_descuento", "tipo_descuento", "fecha_descuento", "valor_descuento")
		->where("dia_descuento", "=", $dia_num)
		->where("tipo_descuento", "=", 0)
		->where("estado_descuento", "=", 1)
		->get();
	}

	// Metodo para traer descuentos para un dia especifico

	public function read_descuentos_por_fecha($fecha) {
		return DB::table("descuentos")
        ->select("dia_descuento", "tipo_descuento", "fecha_descuento", "fecha_fin_descuento", "hora_inicio_descuento", "hora_fin_descuento", "valor_descuento")
        ->whereDate("fecha_descuento", "<=", $fecha)
        ->whereDate("fecha_fin_descuento", ">=", $fecha)
        ->where("tipo_descuento", "=", 1)
        ->where("estado_descuento", "=", 1)
		->get();
	}

	// Metodo para traer descuentos segun la fecha y hora que se escoja

	public function descuentos_fecha_hora($fecha, $hora) {
		$descuentos = $this->read_descuentos_por_fecha($fecha);
		foreach($descuentos as $des) {
			$intervalo_inicio = $des->fecha_descuento." ".$des->hora_inicio_descuento;
			$intervalo_final = $des->fecha_fin_descuento." ".$des->hora_fin_descuento;
			$intervalo_comparar = $fecha." ".$hora;
			if( strtotime($intervalo_comparar) > strtotime($intervalo_inicio) && strtotime($intervalo_comparar) < strtotime($intervalo_final)) {
				return response()->json(["success"=>true]);
			} else {
				return response()->json(["success"=>false]);
			}
		}
		return response()->json($descuentos);
	}

    public function create(DescuentoRequest $request){
        if (isset($request->validator) && $request->validator->fails()) {
	        return response()->json([
				'error_code'=> 'VALIDATION_ERROR', 
				'message'   => 'The given data was invalid.', 
				'errors'    => $request->validator->errors()
			]);
	    } else {
            $Descuento = new Descuento();
			$Descuento->valor_descuento = $request->valor_descuento;
            $Descuento->dia_descuento = $request->dia_descuento;
            $Descuento->tipo_descuento = $request->tipo_descuento;
            $Descuento->fecha_descuento = $request->fecha_descuento;
            $Descuento->fecha_fin_descuento = $request->fecha_fin_descuento;
            $Descuento->hora_inicio_descuento = $request->hora_inicio_descuento;
            $Descuento->hora_fin_descuento = $request->hora_fin_descuento;
            $Descuento->estado_descuento = 1;
            $insertar = $Descuento->save();

            if($insertar){
                $success = true;
                $mensaje = "Descuento creado exitosamente";
            } else {
                $success = false;
                $mensaje = "Error al crear descuento, intentelo mas tarde";
            }
	    }
        
        return response()->json(['success' => $success, 'mensaje' => $mensaje]);
    }

    public function edit($id){
        try {
		    $Descuento_datos = Descuento::findOrFail($id);
            return response()->json(["success" => true, "datos_descuento" => $Descuento_datos]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un descuento con este ID"]);
		}
    }

    public function update(Request $request, $id){
		try {
			$Descuento = Descuento::findOrFail($id);
			$Descuento->valor_descuento = $request->valor_descuento;
            $Descuento->dia_descuento = $request->dia_descuento;
            $Descuento->tipo_descuento = $request->tipo_descuento;
            $Descuento->fecha_descuento = $request->fecha_descuento;
            $Descuento->fecha_fin_descuento = $request->fecha_fin_descuento;
            $Descuento->hora_inicio_descuento = $request->hora_inicio_descuento;
            $Descuento->hora_fin_descuento = $request->hora_fin_descuento;
			$insertar = $Descuento->save();
			if($insertar){
				$success = true;
				$mensaje = "Descuento actualizado exitosamente";
			} else {
				$success = false;
				$mensaje = "Error al actualizar descuento, verifique por favor los datos ingresados";
			}
            return response()->json(["success" => $success, "mensaje" => $mensaje]);
		}
		catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe una Descuento con este ID"]);
		}
        
    }

    public function delete($id){
		try{
			$Descuento = Descuento::findOrFail($id);
			if($Descuento->delete()){
				$success = true;
				$mensaje = "Descuento eliminado con exito";
			} else {
				$success = false;
				$mensaje = "Error al eliminar descuento";
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundHttpException $e)
		{
		    return response()->json(["success" => false, "mensaje" => "Error, No existe un descuento con este ID"]);
		}
	}
	
	public function control_state($id)
	{
		$Sala = Descuento::findOrFail($id);
		if ($Sala->estado_descuento == 1) {
			$Sala->estado_descuento = 0;
			$mensaje = "Descuento inhabilitado correctamente";
		} else {
			$Sala->estado_descuento = 1;
			$mensaje = "Descuento activado correctamente";
		}
		$Sala->save();
		return response()->json(["success" => true, "mensaje" => $mensaje]);
	}
}