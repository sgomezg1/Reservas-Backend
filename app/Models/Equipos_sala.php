<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipos_sala extends Model
{
    protected $table = "equipos_sala";

    protected $primaryKey = "id_equipos_sala";

    public $timestamps = false;

    protected $fillable = [
        'sala_pertenece', 'img_equipo', 'estado_sala', 'nom_equipo'
    ];
}
