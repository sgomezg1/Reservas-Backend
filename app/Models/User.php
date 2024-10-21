<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'nom_banda',
        'nom_registra',
        'ape_registra',
        'telefono_usuario',
        'email',
        'password',
        'rol_usuario',
        'created_at',
        'updated_at',
        'estado_usuario'
    ];

    public function rol() {
        return $this->hasOne(Rol::class, 'id_rol', 'rol_usuario');
    }
}
