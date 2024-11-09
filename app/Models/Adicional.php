<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adicional extends Model
{
    protected $table = "adicional";

    protected $primaryKey = "id_adicional";

    public $timestamps = false;

    protected $fillable = [
        'nom_adicional', 'cant_adicional', 'precio_adicional', 'estado_adicional'
    ];

    public function reservas() {
        return $this->belongsToMany(Reserva::class, 'reserva_adicional', 'adicional_id', 'reserva_id');
    }
}
