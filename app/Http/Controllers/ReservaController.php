<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Multa;
use App\Models\User;
use App\Models\Sala;
use App\Http\Requests\ReservaRequest;
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
use App\Models\CorreosUsuario;

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
		return response()->json([
            "success" => true,
            "inicio" => date('Y-m-d'),
            "fin" =>$primer_dia_dentro_de_dos_meses
        ]);
	}
	// Metodo para traer todas las Reserva en formato JSON para el datatable del administrador

	public function read()
	{
		return response()->json([
            'success' => true,
            'reservas' => Reserva::select("reserva.*")
                ->where("reserva.estado_reserva", true)
                ->get()
        ]);
	}

	// Metodo para traer una reserva completa con multa, sala, usuario y datos de la reserva

	public function read_full_reserva($id)
	{
        $datosReserva = Reserva::select([
            "id_reserva",
            "id_usuario_reserva",
            "sala_reserva",
            "fecha_reserva",
            "hora_reserva"
        ])->with([
            'sala:id_sala,nom_sala',
            'usuario:id,nom_banda,nom_registra,ape_registra',
            'multa:total_multa'
        ])->where("reserva.estado_reserva", true)
            ->where("id_reserva", $id)
            ->first();

		return response()->json([
            'success' => true,
            'reservas' => $datosReserva
        ]);
	}

	// Metodo para obtener todos los ensayos de un mes por banda

	public function read_ensayos_de_mes_por_banda($id, $fecha) {
		$first = date('Y-m-01', strtotime($fecha));
		$last = date('Y-m-t', strtotime($fecha));
		$reservas_de_mes = Reserva::where("id_usuario_reserva", $id)
            ->where("estado_reserva", true)
            ->whereBetween("fecha_reserva", [$first, $last])
            ->get();
		return response()->json([
            'success' => true,
            'cantidad' => count($reservas_de_mes)
        ]);
	}

	// Metodo para traer todas las reservas por usuario mayor al dia actual
	public function read_por_usuario($id)
	{
        $reservasUsuario = Reserva::with([
            'sala:id_sala,nom_sala'
        ])->where("estado_reserva", true)
            ->where("id_usuario_reserva", $id)
            ->where("fecha_reserva", ">=", date("Y-m-d"))
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora_reserva', 'desc')
            ->get();

		return response()->json([
            "success" => true,
            "reservas" => $reservasUsuario
        ]);
	}

	// Metodo para traer reservas por fecha y por sala

	public function read_disponibilidad_por_fecha_y_sala($fecha, $sala) {
        $disponibilidad = Reserva::select("hora_reserva", "fecha_reserva")
            ->where("estado_reserva", true)
			->where("fecha_reserva", $fecha)
			->where("sala_reserva", $sala)
            ->get();
            return response()->json([
                "success" => true,
                "disponibilidad" => $disponibilidad
            ]);
	}

	// Metodo para traer ensayo de una banda en un solo dia

	public function read_ensayo_por_dia_y_banda($fecha) {
        return response()->json([
            'success' => true,
            'cantidad' => count(Reserva::where("fecha_reserva", $fecha)->get())
        ]);
	}

	// Metodo para procesar informacion para calendario de administracion

	public function read_admin()
	{
		$array_convertir = array("id", "title", "start", "end");
		$nuevo_array = array();
		$array_aux = array();
		$objeto_final = new \stdClass();

        $datosReserva = Reserva::with([
            'sala:id_sala,nom_sala',
            'usuario:id,nom_banda'
        ])->where("reserva.estado_reserva", true)
            ->get();
		foreach ($datosReserva as $key => $value) {
			$fecha = $value->fecha_reserva . " " . $value->hora_reserva;
			$fecha_fin = date("Y-m-d H:i:s", strtotime("+2 hour", strtotime($fecha)));
			$nuevo_array[$array_convertir[0]] = $value->id_reserva;
			$nuevo_array[$array_convertir[1]] = "\nBanda:\n" . $value->usuario->nom_banda . "\nSala:\n" . $value->sala->nom_sala;
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
        $datosReserva = Reserva::with([
            'sala:id_sala,nom_sala',
            'usuario:id,nom_banda,nom_registra,ape_registra'
        ])->where("estado_reserva", true)
            ->where("fecha_reserva", $fecha)
            ->where("id_usuario_reserva", $id)
            ->get();
        return response()->json([
            'success' => true,
            'reservas' => $datosReserva
        ]);
	}

	// Metodo para traer todos los ensayos que habran en las salas durante 1 dia

	public function agenda_ensayos_dia_admin($fecha) {
        $datosReserva = Reserva::with([
            'sala:id_sala,nom_sala',
            'usuario:id,nom_banda'
        ])->where("estado_reserva", true)
            ->where("fecha_reserva", $fecha)
            ->get();
        return response()->json([
            'success' => true,
            'reservas' => $datosReserva
        ]);
	}

    private function getDia($fechaReserva) {
        $nombre_dia_actual = date('D', strtotime($fechaReserva));
        $array_dias = array(
            "Sun" => "0",
            "Mon" => "1",
            "Tue" => "2",
            "Wed" => "3",
            "Thu" => "4",
            "Fri" => "5",
            "Sat" => "6"
        );
        return $array_dias[$nombre_dia_actual];
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
		}

        $multa = new MultaController();
        $hay_multa = $multa->validar_multa_usuario($request->id_usuario_reserva);
        if (!$hay_multa) {
            return response()->json([
                "success" => false,
                "mensaje" => "No puedes realizar una reserva porque tienes una multa sin pagar"
            ]);
        }

        $id_usuario = auth()->user()->id;
        if ($request->reservando_usuario) {
            $id_usuario = $request->id_usuario_reserva;
        }

        $fechaActual = date("Y-m-d H:i:s");
        $total = 0;
        $dia = $this->getDia($request->fecha_reserva);

        // Verificamos si hay descuentos activos por fecha o por un dia

        $descuentos = new DescuentoController();
        $descuentos_por_fecha = $descuentos->read_descuentos_por_fecha($request->fecha_reserva);
        $traer_descuentos_parametrizados = $descuentos->read_descuentos_reserva($dia);
        if(!$descuentos_por_fecha->isEmpty()){
            foreach($descuentos_por_fecha as $descuentos) {
                $descuento = ($request->total_precio_reserva * $descuentos->valor_descuento) / 100;
                $total = $request->total_precio_reserva - $descuento;
            }
        } else if (!$traer_descuentos_parametrizados->isEmpty()) {
            foreach($traer_descuentos_parametrizados as $descuentos) {
                $descuento = ($request->total_precio_reserva * $descuentos->valor_descuento) / 100;
                ;
                $total = $request->total_precio_reserva - $descuento;
            }
        } else {
            $total = $request->total_precio_reserva;
        }

        // Llenamos datos para poder realizar la reserva

        $reservaCreada = Reserva::create([
            "sala_reserva" => $request->sala_reserva,
            "fecha_reserva" => $request->fecha_reserva,
            "hora_reserva" => $request->hora_reserva,
            "total_precio_reserva" => $total,
            "precio_cobrado" => 0,
            "estado_confirmacion" => false,
            "id_usuario_reserva" => $id_usuario,
            "estado_reserva" => true,
            "recordatorio_enviado" => false,
            "updated_at" => $fechaActual,
            "created_at" => $fechaActual
        ]);

        if ($request->array_adicionales) {
            foreach ($request->array_adicionales as $key => $adicionales) {
                $Reserva_adicional = new Reserva_adicionalController();
                $Reserva_adicional->create(
                    $adicionales,
                    $reservaCreada->id_reserva,
                    $request->fecha_reserva,
                    $request->hora_reserva
                );
            }
        }

        // Traemos datos de correos adicionales

        $correos_adicionales = CorreosUsuario::where('id_usuario_pert', $id_usuario)->get();

        // Traemos datos de usuario a enviar correo

        $datosUser = User::select('email')
            ->where("id", $id_usuario)
            ->first();

        // Preparamos datos de reserva para armar cada correo

        $datosReserva = Reserva::select([
            "id_reserva",
            "id_usuario_reserva",
            "sala_reserva",
            "fecha_reserva",
            "hora_reserva"
        ])->with([
            'sala:id_sala,nom_sala',
            'usuario:id,nom_banda,nom_registra'
        ])->where('id_reserva', $reservaCreada->id_reserva)
            ->first();

        // Enviamos correo de notifiacion al usuario que realizo la reserva

        // Mail::to($datosUser->email)->send(new ReservaRealizada($datosReserva));

        // Enviamos correos de notificacion a los demas correos

        /* foreach($correos_adicionales as $val) {
            Mail::to($val->dir_correo)->send(new ReservaRealizada($datosReserva));
        } */

        if (!$reservaCreada) {
            return response()->json([
                'success' => false,
                'mensaje' => "Error al crear reserva, intentelo mas tarde"
            ]);
        }
        return response()->json([
            'success' => true,
            'mensaje' => "Reserva creada exitosamente"
        ]);
	}

	public function edit($id)
	{
		return response()->json([
            "success" => true,
            "reserva" => Reserva::findOrFail($id)
        ]);
	}

	public function update(Request $request, $id)
	{
		$reserva = Reserva::findOrFail($id);
        $reserva->sala_reserva = $request->sala_reserva;
        $reserva->fecha_reserva = $request->fecha_reserva;
        $reserva->hora_reserva = $request->hora_reserva;
        $insertar = $reserva->save();
        if ($insertar) {
            $success = true;
            $mensaje = "Reserva actualizada exitosamente";
        } else {
            $success = false;
            $mensaje = "Error al actualizar reserva, verifique por favor los datos ingresados";
        }
        return response()->json(["success" => $success, "mensaje" => $mensaje]);
	}

	// Metodo para actualizar el precio cobrado de la reserva a cobrar

	public function update_precio_cobrado(Request $request, $id) {
		$Reserva = Reserva::findOrFail($id);
        $Reserva->precio_cobrado = $request->precio_cobrado;
        $insertar = $Reserva->save();
        if (!$insertar) {
            return response()->json([
                "success" => false,
                "mensaje" => "Error al finalizar reserva"
            ]);
        }
        return response()->json([
            "success" => true,
            "mensaje" => "Valor modificado correctamente"
        ]);
	}

	// Metodo para cambiar estado de reserva en caso de que asista o no la banda a ensayar

	public function cambiar_estado_asistencia_ensayo(Request $request, $id) {
		$reserva = Reserva::findOrFail($id);
        $estado_confirmacion = $request->estado_confirmacion;
        if (!$estado_confirmacion) {
            $precioMulta = $this->crear_multa(
                $reserva->sala_reserva,
                $id,
                $reserva->id_usuario_reserva
            );
            $reserva->estado_reserva = false;
            $reserva->estado_confirmacion = $estado_confirmacion;
            if (!$reserva->save()) {
                return response()->json([
                    "success" => false,
                    "mensaje" => "Error al editar reserva"
                ]);
            }
            return response()->json([
                "success" => true,
                "mensaje" => "Inasistencia confirmada correctamente. La multa a esta banda es de $" . $precioMulta . " que debe ser cobrada en su proximo ensayo, o pagada antes de realizar una nueva reserva"
            ]);
        }

        $reserva->estado_reserva = false;
        $reserva->estado_confirmacion = $estado_confirmacion;
        $reserva->precio_cobrado = $request->precio_cobrado;
        if (!$reserva->save()) {
            return response()->json([
                "success" => false,
                "mensaje" => "Error al editar reserva"
            ]);
        }
        return response()->json([
            "success" => true,
            "mensaje" => "Reserva finalizada exitosamente"
        ]);
	}

	public function delete($id)
	{
		$reserva = Reserva::findOrFail($id);
        $carbon = new \Carbon\Carbon();
        $fecha_actual = $carbon->now();
        $fecha_reserva = $carbon->parse($reserva->fecha_reserva . " " . $reserva->hora_reserva);
        if ($fecha_actual->diffInSeconds($fecha_reserva) < 86400) {
            $precioMulta = $this->crear_multa($reserva->sala_reserva, $id, $reserva->id_usuario_reserva);
            $reserva->estado_reserva = false;
            if (!$reserva->save()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al eliminar reserva'
                ]);
            }
            return response()->json([
                'success' => true,
                'mensaje' => "Reserva eliminada correctamente. Recuerde que tiene ahora una multa de " . $precioMulta . " por cancelar antes de 24 horas su ensayo"
            ]);
        } else {
            $reserva->estado_reserva = false;
            if (!$reserva->save()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al eliminar reserva'
                ]);
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Reserva eliminada correctamente'
            ]);
        }
	}

	public function control_state($id)
	{
		$reserva = Reserva::findOrFail($id);
        $reserva->estado_reserva = !$reserva->estado_reserva;
		$reserva->save();
		if (!$reserva->estado_reserva) {
            return response()->json([
                "success" => false,
                "mensaje" => "Reserva inhabilitada correctamente"
            ]);
		} else {
            return response()->json([
                "success" => true,
                "mensaje" => "La Reserva esta habilitada ahora mismo"
            ]);
		}
	}

	// Metodo para validar si el dia actual es festivo

	public function validar_festivo()
	{
		$fecha_actual = date("Y-m-d");
		$clase_festivos = new FestivosController();
		$festivos = $clase_festivos->listado_festivos(date("Y"));
		if (!in_array($fecha_actual, $festivos)) {
			return false;
		}
        return true;
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
		$horarios_ocupados = Reserva::select("hora_reserva", "estado_reserva")
			->where("fecha_reserva", "=", $fecha)
			->where("sala_reserva", "=", $sala)
			->get();
		$array_horarios_ocupados = json_decode($horarios_ocupados, true);
		$array_horarios = $this->validar_horarios_festivo_o_dia_normal($fecha);
		$array_disponibles = array();
		if (sizeof($array_horarios_ocupados) == 0) {
			return response()->json([
                "success" => true,
                "array" => $array_horarios
            ]);
		} else if (sizeof($array_horarios_ocupados) >= 1 || sizeof($array_horarios_ocupados) <= 10) {
			$estanOcupados = array();
			foreach ($array_horarios_ocupados as $key) {
				if ($key['estado_reserva']) {
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
				if ($valid) {
					array_push($array_disponibles, $array_horarios[$i]);
				}
			}
			return response()->json([
                "success" => true,
                "array" => $array_disponibles
            ]);
		} else {
			return response()->json([
                "success" => true,
                "mensaje" => "No hay horarios disponibles para el dia seleccionado"
            ]);
		}
	}

	// Metodo para traer los datos de una reserva para ser previsualizados por el administrador

	public function datos_reserva_admin($id)
	{
		$reservas = Reserva::with([
            'sala:id_sala,nom_sala',
            'usuario:id,nom_banda'
        ])->where('id_reserva', $id)
            ->first();
		return response()->json([
            "success" => true,
            "reserva" => $reservas
        ]);
	}

	// Metodo para enviar recordatorios a los usuarios que realizaron una reserva (Recordatorio 36 horas antes del ensayo)

	public function enviar_recordatorio_por_banda()
	{
        $reservas_totales = Reserva::with([
            'sala:id_sala,nom_sala',
            'usuario:id,nom_banda,email'
        ])->get();
		$fecha_actual = date("Y-m-d H:i:s");
		$fecha_datetime_uno = new DateTime($fecha_actual);
		foreach ($reservas_totales as $res) {
			$fecha_datetime_dos = new DateTime($res->fecha_reserva . " " . $res->hora_reserva);
			$diferencia_horas = $fecha_datetime_dos->diff($fecha_datetime_uno);
			if ($diferencia_horas->format("%H") < 36) {
				if (!$res->recordatorio_enviado) {
					$correos = CorreosUsuario::select('dir_correo')
                        ->where('id_usuario_pert', $res->id_usuario)
                        ->get();
					Mail::to($res->email)->send(new RecordatorioMail($res));
					$reserva = Reserva::findOrFail($res->id_reserva);
					$reserva->recordatorio_enviado = true;
					$reserva->save();
					if($correos) {
						foreach($correos as $cor) {
							Mail::to($cor->dir_correo)->send(new RecordatorioMail($res));
						}
					}
				}
			}
		}
	}

    private function crear_multa($salaReserva, $idReserva, $idUsuarioReserva) {
        $Sala = Sala::findOrFail($salaReserva);
        $total_multa = $Sala->precio_sala / 2;
        Multa::create([
            "reserva_id" => $idReserva,
            "total_multa" => $total_multa,
            "usuario_multa" => $idUsuarioReserva,
            "estado_multa" => true
        ]);
        return $total_multa;
    }
}
