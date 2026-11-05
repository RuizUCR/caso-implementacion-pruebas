<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EstudianteFactory extends Factory
{
    // Al crearlos hay que pasar consejero_id: Estudiante::factory()->create(['consejero_id' => $id])
    public function definition(): array
    {
        return [
            // Carné único; el rango evita chocar con C12345
            'carne' => 'C' . fake()->unique()->numberBetween(20000, 99999),
            'nombre' => fake()->firstName(),
            'apellidos' => fake()->lastName() . ' ' . fake()->lastName(),
        ];
    }
}