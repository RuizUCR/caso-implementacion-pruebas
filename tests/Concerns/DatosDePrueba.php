<?php

namespace Tests\Concerns;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;

trait DatosDePrueba
{
    protected function crearCurso(): Curso
    {
        return Curso::create([
            'sigla' => 'IF6100',
            'nombreCurso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);
    }

    protected function crearDocente(int $codigo): Docente
    {
        $datos = [
            1001 => ['Ana', 'Mora Vargas'],
            1002 => ['Luis', 'Rojas Solano'],
            1003 => ['Carla', 'Jiménez Araya'],
        ][$codigo];

        return Docente::create([
            'codEmpleado' => $codigo,
            'nombreEmpleado' => $datos[0],
            'apellidosEmpleado' => $datos[1],
        ]);
    }

    protected function crearGrupo01(Curso $curso, bool $abierto = false): Grupo
    {
        return Grupo::create([
            'curso_id' => $curso->id,
            'numeroCiclo' => 2,
            'ano' => 2026,
            'numeroGrupo' => 1,
            'abierto' => $abierto,
        ]);
    }

    protected function matricularEstudiantes(Grupo $grupo, Docente $consejero, int $cantidad): void
    {
        $estudiantes = Estudiante::factory()
            ->count($cantidad)
            ->create(['consejero_id' => $consejero->id]);

        $grupo->estudiantes()->attach($estudiantes->pluck('id')->all());
    }
}