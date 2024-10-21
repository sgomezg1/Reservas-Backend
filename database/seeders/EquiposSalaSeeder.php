<?php

namespace Database\Seeders;

use App\Models\EquiposSala;
use Illuminate\Database\Seeder;

class EquiposSalaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EquiposSala::factory(16)->create();
    }
}
