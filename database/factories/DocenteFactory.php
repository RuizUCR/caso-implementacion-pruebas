<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DocenteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codEmpleado' => fake()->unique()->numberBetween(2000, 99999),
            'nombreEmpleado' => fake()->firstName(),
            'apellidosEmpleado' => fake()->lastName() . ' ' . fake()->lastName(),
        ];
    }
}