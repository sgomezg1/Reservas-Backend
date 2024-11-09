<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Adicional;
use App\Http\Requests\AdicionalRequest;
use App\Models\Reserva;
use App\Models\ReservaAdicional;
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
		return response()->json([
            "success" => true,
            "adicionales" => Adicional::all()
        ]);
	}

	public function adicionales_para_reserva($id) {
        $adicionalesParaReserva = Reserva::with('adicionales')
            ->where("id_reserva", $id)
            ->first();
        return response()->json([
            "success" => true,
            "adicionales" => $adicionalesParaReserva
        ]);
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
            $adicional->estado_adicional = true;
            $insertar = $adicional->save();
            if(!$insertar){
                return response()->json([
                    'success' => false,
                    'mensaje' => "Error al crear adicional, intentelo mas tarde"
                ]);
            }
            return response()->json([
                'success' => true,
                'mensaje' => "Adicional creado exitosamente"
            ]);
	    }


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
        $mensaje = "El Adicional esta habilitado ahora mismo";
        if(!$adicional->estado_adicional){
            $mensaje = "Adicional inhabilitado correctamente";
        }
		return response()->json([
            "success" => true,
            "mensaje" => $mensaje
        ]);
    }
}
