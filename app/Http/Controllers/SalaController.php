<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sala;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SalaRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Storage;

class SalaController extends Controller
{
	protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(response()->json($validator->errors(), 422));
	}
	// Metodo para traer todas las Sala en formato JSON para el datatable del administrador

	public function read()
	{
		return response()->json([
            "success" => true,
            "salas" => Sala::all()
        ]);
	}

	// Metodo para obtener todas las salas activas

	public function read_activas()
	{
		return response()->json([
            "success" =>true,
            "salas"=> Sala::where('estado_sala', 1)->get()
        ]);
	}

	// Metodo para traer los nombres de las salas activas

	public function nombre_salas() {
        return response()->json([
            "success" =>true,
            "salas"=> Sala::select('nom_sala')->where('estado_sala', true)->get()
        ]);
	}

	// Metodo para traer los datos de una sala junto con sus equipos

	public function salas_con_equipos($id) {
        $sala = Sala::with('equiposSala')
            ->where('id_sala', $id)
            ->where('estado_sala', true)
            ->first();
		return response()->json([
            'success' => true,
            'sala' => $sala
        ]);
	}

	public function create(SalaRequest $request)
	{
		if (isset($request->validator) && $request->validator->fails()) {
			return response()->json([
				'error_code' => 'VALIDATION_ERROR',
				'message'   => 'The given data was invalid.',
				'errors'    => $request->validator->errors()
			]);
		} else {
			$Sala = new Sala();
			$Sala->nom_sala = $request->nom_sala;
			$Sala->precio_sala = $request->precio_sala;
			$Sala->estado_sala = true;
			$Sala->updated_at = date("Y-m-d H:i:s");
			$Sala->created_at = date("Y-m-d H:i:s");
			if ($request->hasFile('foto_sala')) {
				$image = $request->file('foto_sala');
				$name = "imagen" . time() . '.' . $image->getClientOriginalExtension();
                Storage::disk('local')->put($name, file_get_contents($image));
				$Sala->foto_sala = $name;
				$insertar = $Sala->save();
				if ($insertar) {
					$success = true;
					$mensaje = "Sala creada exitosamente";
				} else {
					$success = false;
					$mensaje = "Error al crear sala, intentelo mas tarde";
				}
			} else {
				$success = false;
				$mensaje = "Error, debes subir una imagen para crear una sala";
			}
		}
		return response()->json(['success' => $success, 'mensaje' => $mensaje]);
	}

	public function edit($id)
	{
		$Sala_datos = Sala::findOrFail($id);
			return response()->json(["success" => true, "datos_sala" => $Sala_datos]);
	}

	public function update(Request $request, $id)
	{
        $Sala = Sala::findOrFail($id);
        if ($request->hasFile("foto_sala")) {
            $image = $request->file('foto_sala');
            $name = "imagen" . time() . '.' . $image->getClientOriginalExtension();
            Storage::disk('local')->put($name, file_get_contents($image));
            $Sala->foto_sala = $name;
        } else {
            $Sala->foto_sala = $Sala->foto_sala;
        }
        $Sala->nom_sala = $request->nom_sala;
        $Sala->precio_sala = $request->precio_sala;
        $Sala->estado_sala = "1";
        $Sala->updated_at = date("Y-m-d H:i:s");
        $Sala->created_at = date("Y-m-d H:i:s");
        $insertar = $Sala->save();
        if ($insertar) {
            $success = true;
            $mensaje = "Sala actualizada exitosamente";
        } else {
            $success = false;
            $mensaje = "Error al actualizar sala, verifique por favor los datos ingresados";
        }
        return response()->json(["success" => $success, "mensaje" => $mensaje]);
	}

	public function delete($id)
	{
		$Sala = Sala::findOrFail($id);
        if ($Sala->delete()) {
            File::delete($Sala->foto_sala);
            $success = true;
            $mensaje = "Sala eliminada con exito";
        } else {
            $success = false;
            $mensaje = "Error al eliminar recurso sala  ";
        }
        return response()->json(["success" => $success, "mensaje" => $mensaje]);
	}

	public function control_state($id)
	{
		$Sala = Sala::findOrFail($id);
		if ($Sala->estado_sala == 1) {
			$Sala->estado_sala = 0;
			$mensaje = "Sala inhabilitada correctamente";
		} else {
			$Sala->estado_sala = 1;
			$mensaje = "La sala esta habilitada ahora mismo";
		}
		$Sala->save();
		return response()->json(["success" => true, "mensaje" => $mensaje]);
	}
}
