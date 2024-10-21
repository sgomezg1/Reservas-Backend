<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    protected $table = "descuentos";

    protected $primaryKey = "id_descuento";

    public $timestamps = false;

    protected $fillable = [
        'valor_descuento', 'dia_descuento', 'tipo_descuento', 'fecha_descuento', 'hora_inicio_descuento', 'hora_fin_descuento', 'fecha_fin_descuento', 'estado_descuento'
    ];
}
