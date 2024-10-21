<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reserva;
use App\Multa;
use App\Sala;
use App\Http\Requests\ReservaRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Reserva_adicionalController;
use Illuminate\Support\Facades\Mail;
use DateTime;
use App\Mail\RecordatorioMail;
use App\Http\Controllers\MultaController;
use App\Http\Controllers\FestivosController;
use App\Http\Controllers\DescuentoController;
use App\Mail\ReservaRealizada;

class ReservaController extends Controller
{
	protected function failedValidation(Validator $validator)
	{
		throw new HttpResponseException(response()->json($validator->errors(), 422));
	}

	// Metodo para traer primer dia del mes actual y el ultimo dia del siguiente

	public function primer_dia_ultimo_dia_entre_meses() {
		$lastDateOfNextMonth =strtotime('last day of next month') ;
		$lastDay = date('Y-m-d', $lastDateOfNextMonth);
		$primer_dia_dentro_de_dos_meses = date('Y-m-d', strtotime($lastDay. ' + 1 days'));
		return response()->json(["inicio" => date('Y-m-d'), "fin" =>$primer_dia_dentro_de_dos_meses]);
	}
	// Metodo para traer todas las Reserva en formato JSON para el datatable del administrador

	public function read()
	{
		$Reserva = new Reserva();
		$Reserva_reenviar = DB::table("reserva")
			->select("reserva.*")
			->where("reserva.estado_reserva", "=", 1)
			->get();
		return response()->json($Reserva_reenviar);
	}

	// Metodo para traer una reserva completa con multa, sala, usuario y datos de la reserva

	public function read_full_reserva($id)
	{
		$Reserva_reenviar = DB::table("reserva")
			->join("sala", "reserva.sala_reserva", "sala.id_sala")
			->join("usuario", "usuario.id_usuario", "reserva.id_usuario_reserva")
			->join("multa", "reserva.id_reserva", "multa.reserva_id")
			->select("reserva.id_reserva", "reserva.fecha_reserva", "reserva.hora_reserva", "sala.nom_sala", "usuario.email", "usuario.nom_registra", "usuario.ape_registra", "multa.total_multa")
			->where("reserva.estado_reserva", "=", 1)
			->where("multa.reserva_id", "=", $id)
			->first();
		return response()->json($Reserva_reenviar);
	}

	// Metodo para obtener todos los ensayos de un mes por banda

	public function read_ensayos_de_mes_por_banda($id, $fecha) {
		$first = date('Y-m-01', strtotime($fecha));
		$last = date('Y-m-t', strtotime($fecha));
		$Reserva = new Reserva();
		$reservas_de_mes = $Reserva::where("id_usuario_reserva", "=", $id)->where("estado_reserva", "=", 1)->whereBetween("fecha_reserva", [$first, $last])->get();
		return response()->json(count($reservas_de_mes));
	}

	// Metodo para traer todas las reservas por usuario

	public function read_por_usuario($id)
	{
		$fecha_actual = date("Y-m-d");
		$Reserva_reenviar = DB::table("reserva")
			->join("sala", "reserva.sala_reserva", "sala.id_sala")
			->join("usuario", "usuario.id_usuario", "reserva.id_usuario_reserva")
			->select("reserva.*", "sala.nom_sala")
			->where("reserva.estado_reserva", "=", 1)
			->where("reserva.id_usuario_reserva", "=", $id)
			->where("reserva.fecha_reserva", ">=", $fecha_actual)
			->orderBy('reserva.fecha_reserva', 'desc')
			->orderBy('reserva.hora_reserva', 'desc')
			->get();
		return response()->json($Reserva_reenviar);
	}

	// Metodo para traer reservas por fecha y por sala

	public function read_disponibilidad_por_fecha_y_sala($fecha, $sala) {
		$Reserva_reenviar = DB::table("reserva")
			->select("reserva.hora_reserva", "reserva.fecha_reserva")
			->where("reserva.estado_reserva", "=", 1)
			->where("reserva.fecha_reserva", "=", $fecha)
			->where("reserva.sala_reserva", "=", $sala)
			->get();
		return response()->json($Reserva_reenviar);
	}

	// Metodo para traer ensayo de una banda en un solo dia

	public function read_ensayo_por_dia_y_banda($fecha) {
		$Reserva = new Reserva();
		$reservas_dia_y_banda = $Reserva::where("fecha_reserva", "=", $fecha)->get();
		return response()->json(count($reservas_dia_y_banda));
	}

	// Metodo para procesar informacion para calendario de administracion

	public function read_admin()
	{
		$array_convertir = array("id", "title", "start", "end");
		$nuevo_array = array();
		$array_aux = array();
		$objeto_final = new \stdClass();
		$Reserva_reenviar = DB::table("reserva")
			->join("sala", "reserva.sala_reserva", "sala.id_sala")
			->join("usuario", "usuario.id_usuario", "reserva.id_usuario_reserva")
			->select("sala.nom_sala", "reserva.*", "usuario.nom_banda")
			->where("reserva.estado_reserva", "=", 1)
			->get();
		foreach ($Reserva_reenviar as $key => $value) {
			$fecha = $value->fecha_reserva . " " . $value->hora_reserva;
			$fecha_fin = date("Y-m-d H:i:s", strtotime("+2 hour", strtotime($fecha)));
			$cont = $key + 1;
			$nuevo_array[$array_convertir[0]] = $value->id_reserva;
			$nuevo_array[$array_convertir[1]] = "\nBanda:\n" . $value->nom_banda . "\nSala:\n" . $value->nom_sala;
			$nuevo_array[$array_convertir[2]] = $fecha;
			$nuevo_array[$array_convertir[3]] = $fecha_fin;

			array_push($array_aux, $nuevo_array);
		}
		$objeto_final->events = $array_aux;
		$objeto_retorno = json_encode($objeto_final);
		return $objeto_retorno;
	}

	// Metodo para traer todos los ensayos que habran en las salas durante 1 dia

	public function traer_todos_ensayos_por_dia($fecha, $id) {
		$reservas = DB::table("reserva")
			->join("sala", "reserva.sala_reserva", "sala.id_sala")
			->join("usuario", "usuario.id_usuario", "reserva.id_usuario_reserva")
			->select("sala.nom_sala", "reserva.*", "usuario.nom_banda")
			->where("reserva.estado_reserva", "=", 1)
			->where("reserva.fecha_reserva", "=", $fecha)
			->where("reserva.id_usuario_reserva", "=", $id)
			->get();
		return response()->json($reservas);
	}

	// Metodo para traer todos los ensayos que habran en las salas durante 1 dia

	public function agenda_ensayos_dia_admin($fecha) {
		$reservas = DB::table("reserva")
			->join("sala", "reserva.sala_reserva", "sala.id_sala")
			->join("usuario", "usuario.id_usuario", "reserva.id_usuario_reserva")
			->select("sala.nom_sala", "reserva.*", "usuario.nom_banda")
			->where("reserva.estado_reserva", "=", 1)
			->where("reserva.fecha_reserva", "=", $fecha)
			->get();
		return response()->json($reservas);
	}

	public function create(ReservaRequest $request)
	{
		date_default_timezone_set('America/Bogota');
		if (isset($request->validator) && $request->validator->fails()) {
			return response()->json([
				'error_code' => 'VALIDATION_ERROR',
				'message'   => 'The given data was invalid.',
				'errors'    => $request->validator->errors()
			]);
		} else {
			$multa = new MultaController();
			$total = 0;
			$hay_multa = $multa->validar_multa_usuario($request->id_usuario_reserva);
			if (!$hay_multa) {
				$success = false;
				$mensaje = "No puedes realizar una reserva porque tienes una multa sin pagar";
			} else {
				$Reserva = new Reserva();

				// Obtenemos token de inicio de sesion
				if ($request->reservando_usuario) {
					$token = JWTAuth::getToken();
					$data = JWTAuth::getPayload($token)->toArray();
					$id_usuario = $data['sub'];
				} else {
					$id_usuario = $request->id_usuario_reserva;
				}
				$array_adicionales = $request->array_adicionales;

				//Lenamos datos para poder realizar bien la reserva

				$Reserva->sala_reserva = $request->sala_reserva;
				$Reserva->fecha_reserva = $request->fecha_reserva;
				$Reserva->hora_reserva = $request->hora_reserva;
				$descuentos = new DescuentoController();
				$descuentos_por_fecha = $descuentos->read_descuentos_por_fecha($request->fecha_reserva);
				if(!$descuentos_por_fecha->isEmpty()){
					foreach($descuentos_por_fecha as $descuentos) {
						$descuento = ($request->total_precio_reserva * $descuentos->valor_descuento) / 100;
						$total = $request->total_precio_reserva - $descuento;
					}
				} else {
					$nombre_dia_actual = date('D', strtotime($request->fecha_reserva));
					$array_dias = array(
						"Sun" => "0",
						"Mon" => "1",
						"Tue" => "2",
						"Wed" => "3",
						"Thu" => "4",
						"Fri" => "5",
						"Sat" => "6"
					);
					$dia = $array_dias[$nombre_dia_actual];
					$traer_descuentos_parametrizados = $descuentos->read_descuentos_reserva($dia);
					if(!$traer_descuentos_parametrizados->isEmpty()){
						foreach($traer_descuentos_parametrizados as $descuentos) {
							$descuento = ($request->total_precio_reserva * $descuentos->valor_descuento) / 100;
							;
							$total = $request->total_precio_reserva - $descuento;
						}
					} else {
						$total = $request->total_precio_reserva;
					}					
				}
				$Reserva->total_precio_reserva = $total;
				$Reserva->precio_cobrado = 0;
				$Reserva->estado_confirmacion = 0;		
				$Reserva->id_usuario_reserva = $id_usuario;
				$Reserva->estado_reserva = 1;
				$Reserva->recordatorio_enviado = 0;
				$Reserva->updated_at = date("Y-m-d H:i:s");
				$Reserva->created_at = date("Y-m-d H:i:s");
				$insertar = $Reserva->save();

				if ($array_adicionales) {
					foreach ($array_adicionales as $key => $adicionales) {
						$Reserva_adicional = new Reserva_adicionalController();
						$Reserva_adicional->create($adicionales, $Reserva->id_reserva, $request->fecha_reserva, $request->hora_reserva);
					}
				}

				// Traemos datos de correos adicionales

				$correos_adicionales = DB::table('correos_usuario')
					->select("dir_correo")
					->where('id_usuario_pert', '=', $id_usuario)
					->get();

				// Traemos datos de usuario a enviar correo

				$usuario_datos = DB::table('usuario')
					->select("email")
					->where("id_usuario", "=", $id_usuario)
					->get();
				
				// Preparamos datos de reserva para armar cada correo
				
				$datos_reserva = DB::table('usuario')
				->join("reserva", "usuario.id_usuario", "reserva.id_usuario_reserva")
				->join("sala", "reserva.sala_reserva", "sala.id_sala")
				->select("usuario.nom_banda", "usuario.nom_registra", "reserva.fecha_reserva", "reserva.hora_reserva", "sala.nom_sala", "usuario.ape_registra")
				->where('reserva.id_reserva', '=', $Reserva->id_reserva)
				->get();

				// Enviamos correo de notifiacion al usuario que realizo la reserva

				Mail::to($usuario_datos[0]->email)->send(new ReservaRealizada($datos_reserva[0]));

				// Enviamos correos de notificacion a los demas correos
				
				foreach($correos_adicionales as $val) {
					Mail::to($val->dir_correo)->send(new ReservaRealizada($datos_reserva[0]));
				}

				if ($insertar) {
					$success = true;
					$mensaje = "Reserva creada exitosamente";
				} else {
					$success = false;
					$mensaje = "Error al crear reserva, intentelo mas tarde";
				}
			}
		}

		return response()->json(['success' => $success, 'mensaje' => $mensaje]);
	}

	public function edit($id)
	{
		try {
			$Reserva_datos = Reserva::findOrFail($id);
			return response()->json(["success" => true, "datos_Reserva" => $Reserva_datos]);
		} catch (ModelNotFoundHttpException $e) {
			return response()->json(["success" => false, "mensaje" => "Error, No existe una Reserva con este ID"]);
		}
	}

	public function update(Request $request, $id)
	{
		try {
			$Reserva = Reserva::findOrFail($id);
			$Reserva->sala_reserva = $request->sala_reserva;
			$Reserva->fecha_reserva = $request->fecha_reserva;
			$Reserva->hora_reserva = $request->hora_reserva;
			$insertar = $Reserva->save();
			if ($insertar) {
				$success = true;
				$mensaje = "Reserva actualizada exitosamente";
			} else {
				$success = false;
				$mensaje = "Error al actualizar reserva, verifique por favor los datos ingresados";
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundHttpException $e) {
			return response()->json(["success" => false, "mensaje" => "Error, No existe una reserva con este ID"]);
		}
	}

	// Metodo para actualizar el precio cobrado de la reserva a cobrar

	public function update_precio_cobrado(Request $request, $id) {
		try {
			$Reserva = Reserva::findOrFail($id);
			$Reserva->precio_cobrado = $request->precio_cobrado;
			$insertar = $Reserva->save();
			if ($insertar) {
				$success = true;
				$mensaje = "Reserva finalizada exitosamente";
			} else {
				$success = false;
				$mensaje = "Error al finalizar reserva";
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundHttpException $e) {
			return response()->json(["success" => false, "mensaje" => "Error, No existe una reserva con este ID"]);
		}
	}

	// Metodo para cambiar estado de reserva en caso de que asista o no la banda a ensayar

	public function cambiar_estado_asistencia_ensayo(Request $request, $id) {
		try {
			$Reserva = Reserva::findOrFail($id);
			$estado_confirmacion = $request->estado_confirmacion;
			switch($estado_confirmacion) {
				case "0":
					// Traemos de base de datos la sala para dividir por la mitad el valor del bloque y sacar la multa
					$Sala = Sala::findOrFail($Reserva->sala_reserva);
					$total_multa = $Sala->precio_sala / 2;

					// Creamos array para enviar a metodo de creacion de multa para la clase multa
					$array_crear_multa = array(
						"reserva_id" => $Reserva->id_reserva,
						"total_multa" => $total_multa,
						"usuario_multa" => $Reserva->id_usuario_reserva,
						"estado_multa" => 1
					);
					$multa = new MultaController();
					$multa->create($array_crear_multa);
					$Reserva->estado_reserva = 0;
					$Reserva->estado_confirmacion = $estado_confirmacion;
					if ($Reserva->save()) {
						$success = true;
						$mensaje = "Inasistencia confirmada correctamente. La multa a esta banda es de $" . $total_multa . " que debe ser cobrada en su proximo ensayo, o pagada antes de realizar una nueva reserva";
					} else {
						$success = false;
						$mensaje = "Error al editar reserva";
					}
				break;
				case "1":
					$Reserva->estado_reserva = 0;
					$Reserva->estado_confirmacion = $estado_confirmacion;
					$Reserva->precio_cobrado = $request->precio_cobrado;
					if ($Reserva->save()) {
						$success = true;
						$mensaje = "Reserva finalizada exitosamente";
					} else {
						$success = false;
						$mensaje = "Error al editar reserva";
					}
				break;
				default:
				break;
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundHttpException $e) {
			return response()->json(["success" => false, "mensaje" => "Error, No existe una reserva con este ID"]);
		}
	}

	public function delete($id)
	{
		try {
			$Reserva = Reserva::findOrFail($id);
			$carbon = new \Carbon\Carbon();
			$fecha_actual = $carbon->now();
			$fecha_reserva = $carbon->parse($Reserva->fecha_reserva . " " . $Reserva->hora_reserva);
			if ($fecha_actual->diffInSeconds($fecha_reserva) < 86400) {

				// Traemos de base de datos la sala para dividir por la mitad el valor del bloque y sacar la multa
				$Sala = Sala::findOrFail($Reserva->sala_reserva);
				$total_multa = $Sala->precio_sala / 2;
				
				// Creamos array para enviar a metodo de creacion de multa para la clase multa
				$array_crear_multa = array(
					"reserva_id" => $Reserva->id_reserva,
					"total_multa" => $total_multa,
					"usuario_multa" => $Reserva->id_usuario_reserva,
					"estado_multa" => 1
				);
				$multa = new MultaController();
				$multa_mostrar = $multa->create($array_crear_multa);
				$Reserva = Reserva::findOrFail($id);
				$Reserva->estado_reserva = 0;
				if ($Reserva->save()) {
					$success = true;
					$mensaje = "Reserva eliminada correctamente. Recuerde que tiene ahora una multa de " . $total_multa . " por cancelar antes de 24 horas su ensayo";
				} else {
					$success = false;
					$mensaje = "Error al eliminar reserva";
				}
			} else {
				$Reserva = Reserva::findOrFail($id);
				$Reserva->estado_reserva = 0;
				if ($Reserva->save()) {
					$success = true;
					$mensaje = "Reserva eliminada correctamente";
				} else {
					$success = false;
					$mensaje = "Error al eliminar reserva";
				}
			}
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		} catch (ModelNotFoundHttpException $e) {
			return response()->json(["success" => false, "mensaje" => "Error, No existe una reserva con este ID"]);
		}
	}

	public function control_state($id)
	{
		$Reserva = Reserva::findOrFail($id);
		if ($Reserva->estado_Reserva == 1) {
			$Reserva->estado_Reserva = 0;
			$mensaje = "Reserva inhabilitada correctamente";
		} else {
			$Reserva->estado_Reserva = 1;
			$mensaje = "La Reserva esta habilitada ahora mismo";
		}
		$Reserva->save();
		return response()->json(["success" => true, "mensaje" => $mensaje]);
	}

	// Metodo para validar si el dia actual es festivo

	public function validar_festivo()
	{
		$fecha_actual = date("Y-m-d");
		$clase_festivos = new FestivosController();
		$festivos = $clase_festivos->listado_festivos(date("Y"));
		if (in_array($fecha_actual, $festivos)) {
			return true;
		} else {
			return false;
		}
	}

	// Metodo para validar que horarios hay disponibles segun si es festivo, fin de semana o dia normal.

	public function validar_horarios_festivo_o_dia_normal($fecha)
	{
		date_default_timezone_set('America/Bogota');
		// Vamos a validar si el dia es festivo, fin de semana o dia corriente
		$nombre_dia_actual = date('D', strtotime($fecha));
		$array_horarios = array(
			"10:00:00",
			"12:00:00",
			"14:00:00",
			"16:00:00",
			"18:00:00",
			"20:00:00"
		);
		switch ($nombre_dia_actual) {
			case "Mon":
			case "Tue":
			case "Wen":
			case "Thu":
			case "Fri":
			case "Sat":
				if ($this->validar_festivo()) {
					unset($array_horarios[5]);
				}
				break;
			case "Sun":
				unset($array_horarios[5]);
				break;
			default:
				break;
		}
		return $array_horarios;
	}

	// Metodo para traer todas las reservas por sala segun el dia que se seleccione en el calendario

	public function horarios_disponibles_calendario($fecha, $sala)
	{
		$array = array();;
		$horarios_ocupados = DB::table("reserva")
			->select("hora_reserva", "estado_reserva")
			->where("fecha_reserva", "=", $fecha)
			->where("sala_reserva", "=", $sala)
			->get();
		$array_horarios_ocupados = json_decode($horarios_ocupados, true);
		// print_r($array_horarios_ocupados);
		$array_horarios = $this->validar_horarios_festivo_o_dia_normal($fecha);
		$array_disponibles = array();
		if (sizeof($array_horarios_ocupados) == 0) {
			$success = true;
			return response()->json(["success" => $success, "array" => $array_horarios]);
		} else if (sizeof($array_horarios_ocupados) >= 1 || sizeof($array_horarios_ocupados) <= 10) {
			// mod nicoooo jajaja
			$estanOcupados = array();
			foreach ($array_horarios_ocupados as $key) {
				if ($key['estado_reserva'] == 1) {
					$hrbd = explode(':', $key['hora_reserva']);
					array_push($estanOcupados, $hrbd[0]);
				}
			}
			for ($i = 0; $i < sizeof($array_horarios); $i++) {
				$hrio = explode(':', $array_horarios[$i]);
				$valid = true;
				foreach ($estanOcupados as $horarioOcup) {
					if ($horarioOcup == $hrio[0]) {
						$valid = false;
						break;
					}
				}
				if ($valid == true) {
					array_push($array_disponibles, $array_horarios[$i]);
				}
			}
			$success = true;
			$array = $array_disponibles;
			return response()->json(["success" => $success, "array" => $array]);
		} else {
			$success = true;
			$array = "";
			$mensaje = "No hay horarios disponibles para el dia seleccionado";
			return response()->json(["success" => $success, "mensaje" => $mensaje]);
		}
	}

	// Metodo para traer los datos de una reserva para ser previsualizados por el administrador

	public function datos_reserva_admin($id)
	{
		$Reserva_reenviar = DB::table("reserva")
			->join("sala", "reserva.sala_reserva", "sala.id_sala")
			->join("usuario", "usuario.id_usuario", "reserva.id_usuario_reserva")
			->select("sala.nom_sala", "reserva.*", "usuario.nom_banda")
			->where("reserva.id_reserva", "=", $id)
			->get();
		return response()->json($Reserva_reenviar);
	}

	// Metodo para enviar recordatorios a los usuarios que realizaron una reserva (Recordatorio 36 horas antes del ensayo)

	public function enviar_recordatorio_por_banda()
	{
		$reservas_totales = DB::table("reserva")
			->join("sala", "reserva.sala_reserva", "sala.id_sala")
			->join("usuario", "usuario.id_usuario", "reserva.id_usuario_reserva")
			->select("sala.nom_sala", "reserva.*", "usuario.nom_banda", "usuario.email", "usuario.id_usuario")
			->get();
		$fecha_actual = date("Y-m-d H:i:s");
		$fecha_datetime_uno = new DateTime($fecha_actual);
		foreach ($reservas_totales as $res) {
			$fecha_datetime_dos = new DateTime($res->fecha_reserva . " " . $res->hora_reserva);
			$diferencia_horas = $fecha_datetime_dos->diff($fecha_datetime_uno);
			if ($diferencia_horas->format("%H") < 36) {
				if ($res->recordatorio_enviado == 0) {
					$correos = DB::table('correos_usuario')->select('dir_correo')->where('id_usuario_pert', '=', $res->id_usuario)->get();
					Mail::to($res->email)->send(new RecordatorioMail($res));
					$Reserva = Reserva::findOrFail($res->id_reserva);
					$Reserva->recordatorio_enviado = 1;
					$Reserva->save();
					if($correos) {
						foreach($correos as $cor) {
							Mail::to($cor->dir_correo)->send(new RecordatorioMail($res));
						}
					}
				}
			}
		}
	}
}
