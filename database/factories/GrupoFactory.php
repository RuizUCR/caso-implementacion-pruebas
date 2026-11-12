<?php

namespace Database\Factories;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrupoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'curso_id' => Curso::factory(),
            'numeroCiclo' => fake()->numberBetween(1, 2),
            'ano' => 2026,
            'numeroGrupo' => fake()->numberBetween(1, 9),
            'abierto' => false,
        ];
    }
}