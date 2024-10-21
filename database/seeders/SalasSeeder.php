<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sala')->insert([
            'nom_sala' => "Platino",
            'foto_sala' => "",
            'precio_sala' => "30000",
            'estado_sala' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);

        DB::table('sala')->insert([
            'nom_sala' => "VIP",
            'foto_sala' => "",
            'precio_sala' => "32000",
            'estado_sala' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);

        DB::table('sala')->insert([
            'nom_sala' => "Preferencia",
            'foto_sala' => "",
            'precio_sala' => "24000",
            'estado_sala' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
        DB::table('sala')->insert([
            'nom_sala' => "Studio",
            'foto_sala' => "",
            'precio_sala' => "26000",
            'estado_sala' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
    }
}
