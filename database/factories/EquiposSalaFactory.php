<?php

namespace Database\Factories;

use App\Models\Sala;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EquiposSalaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sala_pertenece' => Sala::select('id_sala')->inRandomOrder()->first(),
            'img_equipo' => 'https://picsum.photos/200',
            'estado_sala' => true,
            'nom_equipo'=> fake()->word()
        ];
    }
}
