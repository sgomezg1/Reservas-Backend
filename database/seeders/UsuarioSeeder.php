<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'username' => "admin",
            'nom_banda' => "admin",
            'nom_registra' => "admin",
            'ape_registra' => "admin",
            'telefono_usuario' => "1234567",
            'password' => bcrypt('*19001900*'),
            'email' => "sebasgomez5892@gmail.com",
            'estado_usuario' => 1,
            'rol_usuario' => 3,
        ]);
    }
}
