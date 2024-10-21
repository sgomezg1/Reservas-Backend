<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = "reserva";

    protected $primaryKey = "id_reserva";

    protected $fillable = [
        'sala_reserva', 'fecha_reserva', 'hora_reserva', 'recordatorio_enviado', 'id_usuario_reserva', 'updated_at', 'created_at', 'total_precio_reserva', 'precio_cobrado'
    ];
}
