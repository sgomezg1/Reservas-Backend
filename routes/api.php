<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EquiposSalaController;
use App\Http\Controllers\SalaController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function() {
    Route::post('/login', 'login');
    Route::post('/signup', 'signup');
});

Route::prefix('me')->middleware([
    'auth:sanctum',
    'ability:banda,empleado,admin'
])->group(function () {
    Route::get("", [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::prefix('salas')->middleware([
    'auth:sanctum',
    'ability:empleado,admin'
])->group(function() {
    Route::get("", [SalaController::class, "read"]);
    Route::get("/activas", [SalaController::class, "read_activas"]);
    Route::get("/nombres", [SalaController::class, "nombre_salas"]);
    Route::get("/{id}", [SalaController::class, "edit"]);
    Route::get("/equipos/{id}", [SalaController::class, "salas_con_equipos"]);
    Route::post("", [SalaController::class, "create"]);
    Route::post("/{id}", [SalaController::class, "update"]);
    Route::put("/toggle/{id}", [SalaController::class, "control_state"]);
    Route::delete("/{id}", [SalaController::class, "delete"]);
});

// Rutas para el control de equipos

Route::prefix('equipos')->middleware([
    'auth:sanctum',
    'ability:empleado,admin'
])->group(function() {
    Route::get("", [EquiposSalaController::class, "read"]);
    Route::post("", [EquiposSalaController::class, "create"]);
    Route::get("/{id}", [EquiposSalaController::class, "edit"]);
    Route::post("/{id}", [EquiposSalaController::class, "update"]);
    Route::put("/habilitar_deshabilitar_equipo/{id}", [EquiposSalaController::class, "control_state"]);
    Route::delete("/{id}", [SalaController::class, "delete"]);
});

// Rutas para el control de multas

/* Route::get("multas/todas_multas", "MultaController@read");
Route::post("multas/crear_multa", "MultaController@create");
Route::get("multas/datos_multa_editar/{id}", "MultaController@edit");
Route::get("multas/multas_por_usuario/{id}", "MultaController@multas_por_usuario");
Route::put("multas/editar_multa/{id}", "MultaController@update");
Route::put("multas/habilitar_deshabilitar_multa/{id}", "MultaController@control_state");
Route::get("multas/validar_usuario_por_multa/{id}", "MultaController@validar_usuario_con_multa"); */

// Rutas para el control de adicionales

/* Route::get("adicionales/todos_adicionales", "AdicionalController@read");
Route::get("adicionales/adicionales_reserva/{fecha}/{hora}", "AdicionalController@adicionales_para_reserva");
Route::post("adicionales/crear_adicional", "AdicionalController@create");
Route::get("adicionales/datos_adicional_editar/{id}", "AdicionalController@edit");
Route::put("adicionales/editar_adicional/{id}", "AdicionalController@update");
Route::put("adicionales/habilitar_deshabilitar_adicional/{id}", "AdicionalController@control_state"); */

// Rutas para el control de descuentos

/* Route::get("descuentos/todos_descuentos", "DescuentoController@read");
Route::get("descuentos/descuentos_por_dia/{fecha}", "DescuentoController@read_descuentos_por_fecha");
Route::get("descuentos/descuentos_por_dia_semana/{fecha}", "DescuentoController@read_descuentos_reserva");
Route::post("descuentos/crear_descuento", "DescuentoController@create");
Route::get("descuentos/datos_descuento_editar/{id}", "DescuentoController@edit");
Route::put("descuentos/editar_descuento/{id}", "DescuentoController@update");
Route::delete("descuentos/eliminar_descuento/{id}", "DescuentoController@delete");
Route::put("descuentos/hab_deshab_descuento/{id}", "DescuentoController@control_state");
Route::get("descuentos/descuentos_fecha_hora/{fecha}/{hora}", "DescuentoController@descuentos_fecha_hora"); */

// Rutas para obtener datos de reserva_adicional

// Route::get("reserva_adicional/datos_reserva_adicional_por_id_reserva/{id}", "Reserva_adicionalController@datos_reserva_adicional_por_id_reserva");

// Rutas para control de usuarios
/*
Route::get("usuarios/todos_usuarios", "UsuarioController@read");
Route::post("usuarios/crear_usuario", "UsuarioController@create");
Route::get("usuarios/datos_usuario_editar/{id}", "UsuarioController@edit");
Route::put("usuarios/actualizar_usuario/{id}", "UsuarioController@update");
Route::delete("usuarios/eliminar_usuario/{id}", "UsuarioController@delete");
Route::put("usuarios/habilitar_deshabilitar_usuario/{id}", "UsuarioController@control_state");
Route::get("usuarios/enviar_correo_recuperacion_contrasenia/{email}", "UsuarioController@enviar_correo_solicitud_reestablecer_contrasenia");
Route::put("usuarios/actualizar_pass_usuario/{email}/{token}", "UsuarioController@procesar_token_de_respuesta");
Route::get("usuarios/correos_notificacion/{id}", "UsuarioController@correos_notificacion");
Route::put("usuarios/editar_correos_notificacion/{id}", "UsuarioController@editar_correos_notificacion"); */

// Rutas para control de reservas

/* Route::get("reservas/todas_reservas", "ReservaController@read");
Route::get("reservas/todas_reservas_admin", "ReservaController@read_admin");
Route::post("reservas/crear_reserva", "ReservaController@create");
Route::get("reservas/datos_reserva_editar/{id}", "ReservaController@edit");
Route::get("reservas/datos_reserva_por_usuario/{id}", "ReservaController@read_por_usuario");
Route::put("reservas/actualizar_reserva/{id}", "ReservaController@update");
Route::delete("reservas/eliminar_reserva/{id}", "ReservaController@delete");
Route::get("reservas/horarios_disponibles_por_dia/{fecha_reserva}/{id_sala}", "ReservaController@horarios_disponibles_calendario");
Route::get("reservas/datos_reserva_por_id_admin/{id}", "ReservaController@datos_reserva_admin");
Route::put("reservas/activar_reservas", "ReservaController@ocultar_reserva_cada_dos_horas");
Route::put("reservas/enviar_recordatorios", "ReservaController@enviar_recordatorio_por_banda");
Route::put("reservas/confirmar_asistencia_ensayo/{id}", "ReservaController@cambiar_estado_asistencia_ensayo");
Route::get("reservas/reserva_por_fecha_y_sala/{fecha}/{sala}", "ReservaController@read_disponibilidad_por_fecha_y_sala");
Route::get("reservas/traer_fechas_validas_calendario", "ReservaController@primer_dia_ultimo_dia_entre_meses");
Route::get("reservas/ensayos_por_dia/{fecha}/{id}", "ReservaController@traer_todos_ensayos_por_dia");
Route::get("reservas/ensayos_por_dia_admin/{fecha}", "ReservaController@agenda_ensayos_dia_admin");
Route::get("reservas/ensayos_por_mes_por_banda/{id}/{fecha}", "ReservaController@read_ensayos_de_mes_por_banda");
Route::get("reservas/ensayo_por_dia/{fecha}", "ReservaController@read_ensayo_por_dia_y_banda"); */
