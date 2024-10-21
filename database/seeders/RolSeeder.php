<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rol')->insert([
            'id_rol' => 1,
            'nom_rol' => "banda"
        ]);
        DB::table('rol')->insert([
            'id_rol' => 2,
            'nom_rol' => "empleado"
        ]);
        DB::table('rol')->insert([
            'id_rol' => 3,
            'nom_rol' => "admin"
        ]);
    }
}
