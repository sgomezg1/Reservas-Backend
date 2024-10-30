<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EquiposSalaController;
use App\Http\Controllers\MultaController;
use App\Http\Controllers\Reserva_adicionalController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)
    ->prefix('auth')->group(function() {
    Route::post('/login', 'login');
    Route::post('/signup', 'signup');
    Route::get("/enviar_correo_recuperacion_contrasenia/{email}", [UsuarioController::class,"enviar_correo_solicitud_reestablecer_contrasenia"]);
    Route::put("/actualizar_pass_usuario/{email}/{token}", [UsuarioController::class,"procesar_token_de_respuesta"]);
    Route::get("/correos_notificacion/{id}", [UsuarioController::class,"correos_notificacion"]);
    Route::put("/editar_correos_notificacion/{id}", [UsuarioController::class,"editar_correos_notificacion"]);
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
    'ability:admin'
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
    'ability:admin'
])->group(function() {
    Route::get("", [EquiposSalaController::class, "read"]);
    Route::post("", [EquiposSalaController::class, "create"]);
    Route::get("/{id}", [EquiposSalaController::class, "edit"]);
    Route::post("/{id}", [EquiposSalaController::class, "update"]);
    Route::put("/habilitar_deshabilitar_equipo/{id}", [EquiposSalaController::class, "control_state"]);
    Route::delete("/{id}", [SalaController::class, "delete"]);
});

Route::prefix('usuarios')->middleware([
    'auth:sanctum',
    'ability:empleado,admin'
])->group(function() {
    Route::get("", [UsuarioController::class,"read"]);
    Route::post("", [AuthController::class,"signup"]);
    Route::get("/{id}", [UsuarioController::class,"edit"]);
    Route::put("/{id}", [UsuarioController::class,"update"]);
    Route::delete("/{id}", [UsuarioController::class,"delete"]);
    Route::put("/toggle/{id}", [UsuarioController::class,"control_state"]);
});

Route::prefix('reservas')->middleware([
    'auth:sanctum',
    'ability:empleado,admin,banda'
])->group(function() {
    Route::get("", [ReservaController::class, "read"]);
    Route::get("/{id}", [ReservaController::class, "edit"]);
    Route::get("admin", [ReservaController::class, "read_admin"]);
    Route::get("admin/{id}", [ReservaController::class, "datos_reserva_admin"]);
    Route::get("ensayos/dia/all/{fecha}/{id}", [ReservaController::class, "traer_todos_ensayos_por_dia"]);
    Route::get("ensayos/dia/admin/{fecha}", [ReservaController::class, "agenda_ensayos_dia_admin"]);
    Route::get("ensayos/mes/admin/{id}/{fecha}", [ReservaController::class, "read_ensayos_de_mes_por_banda"]);
    Route::get("ensayos/banda/dia/{fecha}", [ReservaController::class, "read_ensayo_por_dia_y_banda"]);
    Route::get("/user/{id}", [ReservaController::class, "read_por_usuario"]);
    Route::get("/horarios/dia/{fecha_reserva}/{id_sala}", [ReservaController::class, "horarios_disponibles_calendario"]);
    Route::get("/fecha/sala/{fecha}/{sala}", [ReservaController::class, "read_disponibilidad_por_fecha_y_sala"]);
    Route::get("/fechas/calendario", [ReservaController::class, "primer_dia_ultimo_dia_entre_meses"]);
    Route::post("", [ReservaController::class, "create"]);
    Route::put("/{id}", [ReservaController::class, "update"]);
    Route::delete("/{id}", [ReservaController::class, "delete"]);
    Route::put("asistencia/{id}", [ReservaController::class, "cambiar_estado_asistencia_ensayo"]);
    // TODO: Hacer funcionar los correos
    Route::put("recordatorios", [ReservaController::class, "enviar_recordatorio_por_banda"]);
    // TODO: Corregir este endpoint al finalizar el trabajo con adicionales
    Route::get("adicionales/{id}", [Reserva_adicionalController::class, "datos_reserva_adicional_por_id_reserva"]);
});

/* Route::prefix('multas')->middleware([
    'auth:sanctum',
    'ability:empleado,admin'
])->group(function() {
    Route::get("", [MultaController::class, "read"]);
    Route::get("/{id}", [MultaController::class, "edit"]);
    Route::get("/usuario/{id}", [MultaController::class, "multas_por_usuario"]);
    Route::get("/usuario_multa/{id}", [MultaController::class, "validar_usuario_con_multa"]);
    Route::post("", [MultaController::class, "create"]);
    Route::put("/{id}", [MultaController::class, "update"]);
    Route::put("/toggle/{id}", [MultaController::class, "control_state"]);
}); */

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
