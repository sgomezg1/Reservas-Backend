<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorreosUsuario extends Model
{
    protected $table = 'correos_usuario';

    protected $primaryKey = 'id_correo_usuario';

    public $timestamps = false;

    protected $fillable = [
        'dir_correo',
        'id_usuario_pert'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
