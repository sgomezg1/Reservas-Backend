<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservaAdicional extends Model
{
    protected $table = "reserva_adicional";

    protected $primaryKey = "id_sala_adicional";

    protected $fillable = [
        'reserva_id', 'adicional_id', 'updated_at', 'created_at', 'fecha_reserva', 'hora_reserva'
    ];
}
