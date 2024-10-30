<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = "reserva";

    protected $primaryKey = "id_reserva";

    protected $fillable = [
        'sala_reserva',
        'fecha_reserva',
        'hora_reserva',
        'recordatorio_enviado',
        'id_usuario_reserva',
        'updated_at',
        'created_at',
        'total_precio_reserva',
        'precio_cobrado',
        'estado_reserva',
        'estado_confirmacion'
    ];

    protected $hidden = ['updated_at', 'created_at'];

    public function sala() {
        return $this->hasOne(Sala::class, "id_sala", "sala_reserva");
    }

    public function usuario() {
        return $this->belongsTo(User::class, "id_usuario_reserva");
    }

    public function multa() {
        return $this->belongsTo(Multa::class, "id_reserva", "reserva_id");
    }
}
