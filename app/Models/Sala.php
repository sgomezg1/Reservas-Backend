<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    protected $table = "sala";

    protected $primaryKey = "id_sala";

    protected $fillable = [
        'nom_sala', 'foto_sala', 'estado_sala', 'updated_at', 'created_at', 'precio_sala'
    ];
}
