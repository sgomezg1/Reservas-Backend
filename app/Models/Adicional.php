<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adicional extends Model
{
    protected $table = "adicional";

    protected $primaryKey = "id_adicional";

    public $timestamps = false;

    protected $fillable = [
        'precio_adicional', 'precio_adicional', 'precio_adicional', 'estado_adicional'
    ];
}
