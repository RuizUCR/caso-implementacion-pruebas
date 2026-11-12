<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CursoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sigla' => 'FX' . fake()->unique()->numberBetween(1000, 9999),
            'nombreCurso' => fake()->sentence(3),
            'creditos' => fake()->numberBetween(1, 6),
        ];
    }
}