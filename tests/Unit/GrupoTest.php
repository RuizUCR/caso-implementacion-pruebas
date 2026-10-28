<?php

namespace Tests\Unit;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrupoTest extends TestCase
{
    use RefreshDatabase;

    public function test_grupo_contiene_toda_su_informacion(): void
    {
        $docente1 = Docente::create([
            'codEmpleado' => 1001,
            'nombreEmpleado' => 'Gerald',
            'apellidosEmpleado' => 'Gutierrez Peralta',
        ]);

        $docente2 = Docente::create([
            'codEmpleado' => 1002,
            'nombreEmpleado' => 'Luis',
            'apellidosEmpleado' => 'Acosta Mendoza',
        ]);

        $curso = Curso::create([
            'sigla' => 'ECCI-101',
            'nombreCurso' => 'Programación I',
            'creditos' => 4,
        ]);

        $grupo = Grupo::create([
            'curso_id' => $curso->id,
            'numeroCiclo' => 2,
            'ano' => 2026,
            'numeroGrupo' => 1,
            'abierto' => true,
        ]);

        $grupo->docentes()->attach([$docente1->id, $docente2->id]);

        for ($i = 1; $i <= 15; $i++) {
            $estudiante = Estudiante::create([
                'carne' => 'C' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'nombre' => "Estudiante $i",
                'apellidos' => 'Apellido Prueba',
                'consejero_id' => $docente1->id,
            ]);

            $grupo->estudiantes()->attach($estudiante->id, ['nota' => 80.5]);
        }

        $grupo = Grupo::find($grupo->id);

        // Atributos propios del grupo
        $this->assertSame(2, $grupo->numeroCiclo);
        $this->assertSame(2026, $grupo->ano);
        $this->assertSame(1, $grupo->numeroGrupo);
        $this->assertTrue($grupo->abierto);

        // Curso asociado
        $this->assertSame('ECCI-101', $grupo->curso->sigla);
        $this->assertSame('Programación I', $grupo->curso->nombreCurso);
        $this->assertSame(4, $grupo->curso->creditos);

        // Docentes (1..2)
        $this->assertCount(2, $grupo->docentes);
        $this->assertTrue($grupo->docentes->contains($docente1));
        $this->assertTrue($grupo->docentes->contains($docente2));

        // Estudiantes matriculados (15..*) con su nota
        $this->assertCount(15, $grupo->estudiantes);
        $this->assertSame(80.5, $grupo->estudiantes->first()->pivot->nota);

        // Consejero del estudiante
        $this->assertSame('Gerald', $grupo->estudiantes->first()->consejero->nombreEmpleado);
    }
}
