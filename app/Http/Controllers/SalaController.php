<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sala;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SalaRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
		$sala = new Sala();
		$sala_reenviar = Sala::where('estado_sala', 1)->get();
		return response()->json(["salas"=> $sala_reenviar]);
	}

	// Metodo para traer los nombres de las salas activas

	public function nombre_salas() {
		$sala_reenviar = Sala::where('estado_sala', 1)->get(['nom_sala']);
		return response()->json($sala_reenviar);
	}

	// Metodo para traer titulo y ID de las salas para llenar el resources de fullcalendar en su administrador

	public function read_para_resources()
	{

		$array_convertir = array("id", "title");
		$nuevo_array = array();
		$array_aux = array();
		$objeto_final = new \stdClass();
		$salas_resources = Sala::select('id_sala', 'nom_sala')->where('estado_sala', 1)->get();
		foreach ($salas_resources as $key => $value) {
			$nuevo_array[$array_convertir[0]] = $value->id_sala;
			$nuevo_array[$array_convertir[1]] = $value->nom_sala;
			array_push($array_aux, $nuevo_array);
		}
		$objeto_final->events = $array_aux;
		$objeto_retorno = json_encode($objeto_final);
		return $objeto_retorno;
	}

	// Metodo para traer los datos de una sala junto con sus equipos

	public function salas_con_equipos($id) {
		$imagen_sala = DB::table('sala')
		->select('sala.foto_sala')
		->where('sala.id_sala', '=', $id)
		->where('sala.estado_sala', '=', 1)
		->get();

		$equipos_sala = DB::table('equipos_sala')
		->select('equipos_sala.nom_equipo', 'equipos_sala.img_equipo')
		->where('equipos_sala.estado_sala', '=', 1)
		->where('equipos_sala.sala_pertenece', '=', $id)
		->get();

		return response()->json(['sala' => $imagen_sala, 'equipos_sala' => $equipos_sala]);
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
			$Sala->estado_sala = "1";
			$Sala->updated_at = date("Y-m-d H:i:s");
			$Sala->created_at = date("Y-m-d H:i:s");
			if ($request->hasFile('foto_sala')) {
				$image = $request->file('foto_sala');
				$name = "imagen" . time() . '.' . $image->getClientOriginalExtension();
				$destinationPath = public_path('/images');
				$image->move($destinationPath, $name);
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
		try {
			$Sala_datos = Sala::findOrFail($id);
			return response()->json(["success" => true, "datos_sala" => $Sala_datos]);
		} catch (ModelNotFoundHttpException $e) {
			return response()->json(["success" => false, "mensaje" => "Error, No existe una sala con este ID"]);
		}
	}

	public function update(Request $request, $id)
	{
		try {
			$Sala = Sala::findOrFail($id);
			if ($request->hasFile("foto_sala")) {
				$image = $request->file('foto_sala');
				$name = "imagen" . time() . '.' . $image->getClientOriginalExtension();
				$destinationPath = public_path('/images');
				$image->move($destinationPath, $name);
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
		} catch (ModelNotFoundHttpException $e) {
			return response()->json(["success" => false, "mensaje" => "Error, No existe una noticia con este ID"]);
		}
	}

	public function delete($id)
	{
		try {
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
		} catch (ModelNotFoundHttpException $e) {
			return response()->json(["success" => false, "mensaje" => "Error, No existe una noticia con este ID"]);
		}
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
