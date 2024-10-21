<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Multa extends Model
{
    protected $table = "multa";

    protected $primaryKey = "id_multa";

    protected $fillable = [
        'reserva_id', 'total_multa', 'estado_multa', 'updated_at', 'created_at', 'usuario_multa'
    ];
}
