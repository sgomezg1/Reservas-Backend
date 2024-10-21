<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquiposSala extends Model
{
    use HasFactory;

    protected $table = "equipos_sala";

    protected $primaryKey = "id_equipos_sala";

    public $timestamps = false;

    protected $fillable = [
        'sala_pertenece', 'img_equipo', 'estado_sala', 'nom_equipo'
    ];

    public function sala() {
        return $this->belongsToMany(Sala::class);
    }
}
