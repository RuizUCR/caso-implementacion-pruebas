<?php

namespace Tests\Unit;

use App\Models\Grupo;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DatosDePrueba;
use Tests\TestCase;

class GrupoCasosPruebaTest extends TestCase
{
    use RefreshDatabase;
    use DatosDePrueba;

    // CP-01: grupo con toda su información y su curso
    public function test_cp01_registra_un_grupo_con_toda_su_informacion_y_su_curso(): void
    {
        $curso = $this->crearCurso();
        $grupo = $this->crearGrupo01($curso, abierto: true);

        $recuperado = Grupo::find($grupo->id);

        $this->assertSame(2, $recuperado->numeroCiclo);
        $this->assertSame(2026, $recuperado->ano);
        $this->assertSame(1, $recuperado->numeroGrupo);
        $this->assertTrue($recuperado->abierto);
        $this->assertIsBool($recuperado->abierto);

        $this->assertSame('IF6100', $recuperado->curso->sigla);
        $this->assertSame(4, $recuperado->curso->creditos);

        $this->assertDatabaseCount('grupos', 1);
    }

    // CP-02: un curso tiene varios grupos y se distinguen los abiertos
    public function test_cp02_un_curso_tiene_varios_grupos_y_se_distinguen_los_abiertos(): void
    {
        $curso = $this->crearCurso();

        foreach ([1 => true, 2 => true, 3 => false] as $numero => $abierto) {
            Grupo::create([
                'curso_id' => $curso->id,
                'numeroCiclo' => 2,
                'ano' => 2026,
                'numeroGrupo' => $numero,
                'abierto' => $abierto,
            ]);
        }

        $grupos = $curso->grupos;
        $abiertos = $grupos->where('abierto', true);

        $this->assertCount(3, $grupos);
        $this->assertCount(2, $abiertos);
        $this->assertSame([1, 2], $abiertos->pluck('numeroGrupo')->sort()->values()->all());

        $this->assertSame(3, Grupo::where('curso_id', $curso->id)->count());
        $this->assertCount(1, $grupos->pluck('curso_id')->unique());
    }

    // CP-03: un grupo acepta dos docentes
    public function test_cp03_un_grupo_acepta_dos_docentes(): void
    {
        $grupo = $this->crearGrupo01($this->crearCurso());
        $docente1 = $this->crearDocente(1001);
        $docente2 = $this->crearDocente(1002);

        $grupo->asignarDocente($docente1);
        $grupo->asignarDocente($docente2);

        $docentes = $grupo->fresh()->docentes;

        $this->assertCount(2, $docentes);
        $this->assertSame([1001, 1002], $docentes->pluck('codEmpleado')->sort()->values()->all());
        $this->assertTrue($docente1->fresh()->grupos->contains($grupo));

        $this->assertDatabaseCount('grupo_docente', 2);
    }

    // CP-04: un grupo rechaza un tercer docente
    public function test_cp04_un_grupo_rechaza_un_tercer_docente(): void
    {
        $grupo = $this->crearGrupo01($this->crearCurso());
        $grupo->asignarDocente($this->crearDocente(1001));
        $grupo->asignarDocente($this->crearDocente(1002));
        $docente3 = $this->crearDocente(1003);

        $lanzada = false;

        try {
            $grupo->asignarDocente($docente3);
        } catch (DomainException) {
            $lanzada = true;
        }

        $this->assertTrue($lanzada, 'Se esperaba una DomainException');
        $this->assertCount(2, $grupo->fresh()->docentes);
        $this->assertDatabaseCount('grupo_docente', 2);
        $this->assertDatabaseMissing('grupo_docente', ['docente_id' => $docente3->id]);
    }

    // CP-05: con 14 estudiantes no puede abrirse (límite inferior)
    public function test_cp05_un_grupo_con_14_estudiantes_no_puede_abrirse(): void
    {
        $grupo = $this->crearGrupo01($this->crearCurso());
        $docente = $this->crearDocente(1001);
        $grupo->asignarDocente($docente);
        $this->matricularEstudiantes($grupo, $docente, 14);

        $this->assertFalse($grupo->puedeAbrirse());

        $lanzada = false;

        try {
            $grupo->abrir();
        } catch (DomainException) {
            $lanzada = true;
        }

        $this->assertTrue($lanzada, 'Se esperaba una DomainException');
        $this->assertFalse($grupo->fresh()->abierto);
    }

    // CP-06: con 15 estudiantes y un docente se abre (límite)
    public function test_cp06_un_grupo_con_15_estudiantes_y_un_docente_se_abre(): void
    {
        $grupo = $this->crearGrupo01($this->crearCurso());
        $docente = $this->crearDocente(1001);
        $grupo->asignarDocente($docente);
        $this->matricularEstudiantes($grupo, $docente, 15);

        $this->assertTrue($grupo->puedeAbrirse());

        $grupo->abrir();

        $this->assertTrue($grupo->fresh()->abierto);
    }
}