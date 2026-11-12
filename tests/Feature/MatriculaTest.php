<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
use App\Models\Grupo;
use Illuminate\Database\QueryException;

mutates(Grupo::class);

function crearGrupo01(): Grupo
{
    $curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombreCurso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    return Grupo::factory()->for($curso)->create([
        'numeroCiclo' => 2,
        'ano' => 2026,
        'numeroGrupo' => 1,
        'abierto' => false,
    ]);
}

function crearDocente(int $codigo): Docente
{
    $datos = [
        1001 => ['Ana', 'Mora Vargas'],
        1002 => ['Luis', 'Rojas Solano'],
        1003 => ['Carla', 'Jiménez Araya'],
    ][$codigo];

    return Docente::factory()->create([
        'codEmpleado' => $codigo,
        'nombreEmpleado' => $datos[0],
        'apellidosEmpleado' => $datos[1],
    ]);
}

function crearEstudianteC12345(Docente $consejero): Estudiante
{
    return Estudiante::factory()->create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
        'consejero_id' => $consejero->id,
    ]);
}

function matriculaDe(Grupo $grupo, Estudiante $estudiante): ?EstudianteMatriculado
{
    return EstudianteMatriculado::where('grupo_id', $grupo->id)
        ->where('estudiante_id', $estudiante->id)
        ->first();
}

test('CP-07 un grupo sin docente no se abre aunque tenga cupo completo', function () {
    $grupo = crearGrupo01();
    $consejero = crearDocente(1001);
    $estudiantes = Estudiante::factory()->count(20)->create(['consejero_id' => $consejero->id]);
    $grupo->estudiantes()->attach($estudiantes->pluck('id')->all());

    expect($grupo->docentes()->count())->toBe(0);
    expect($grupo->puedeAbrirse())->toBeFalse();
    expect(fn () => $grupo->abrir())->toThrow(DomainException::class);
    expect($grupo->fresh()->abierto)->toBeFalse();
});

test('CP-08 un estudiante queda ligado a su profesor consejero', function () {
    $docente = crearDocente(1001);
    expect($docente->estudiantes()->count())->toBe(0);

    $estudiante = crearEstudianteC12345($docente);

    expect($estudiante->consejero->codEmpleado)->toBe(1001);

    $aconsejados = $docente->fresh()->estudiantes;
    expect($aconsejados)->toHaveCount(1);
    expect($aconsejados->first()->carne)->toBe('C12345');

    expect($estudiante->fresh()->consejero_id)->toBe($docente->id);
});

test('CP-09 cambiar de consejero reemplaza al anterior, no lo suma', function () {
    $docente1 = crearDocente(1001);
    $docente2 = crearDocente(1002);
    $estudiante = crearEstudianteC12345($docente1);

    $estudiante->consejero()->associate($docente2);
    $estudiante->save();

    expect($estudiante->fresh()->consejero->codEmpleado)->toBe(1002);
    expect($docente1->fresh()->estudiantes)->toHaveCount(0);
    expect($docente2->fresh()->estudiantes)->toHaveCount(1);

    expect($estudiante->fresh()->consejero_id)->toBe($docente2->id);
    expect(Estudiante::where('carne', 'C12345')->count())->toBe(1);
});

test('CP-10 al matricular, la nota queda vacía', function () {
    $grupo = crearGrupo01();
    $estudiante = crearEstudianteC12345(crearDocente(1001));

    $grupo->estudiantes()->attach($estudiante->id);

    $pivote = $grupo->fresh()->estudiantes->first()->pivot;

    expect($pivote)->toBeInstanceOf(EstudianteMatriculado::class);
    expect($pivote->nota)->toBeNull();

    $this->assertDatabaseCount('estudiantes_matriculados', 1);
    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $estudiante->id,
    ]);
});

test('CP-11 registra la nota al finalizar el curso', function () {
    $grupo = crearGrupo01();
    $docente = crearDocente(1001);
    $estudiante = crearEstudianteC12345($docente);
    $otro = Estudiante::factory()->create(['consejero_id' => $docente->id]);
    $grupo->estudiantes()->attach([$estudiante->id, $otro->id]);

    $grupo->registrarNota($estudiante, 8.5);

    $matricula = matriculaDe($grupo, $estudiante);
    expect($matricula->nota)->toBe(8.5)->toBeFloat();

    // Solo cambió la nota de C12345; el resto de la matrícula queda igual
    expect($matricula->grupo_id)->toBe($grupo->id);
    expect($matricula->estudiante_id)->toBe($estudiante->id);
    expect(matriculaDe($grupo, $otro)->nota)->toBeNull();
    $this->assertDatabaseCount('estudiantes_matriculados', 2);
});

test('CP-12 rechaza notas menores que 0 o mayores que 10', function (float $nota) {
    $grupo = crearGrupo01();
    $estudiante = crearEstudianteC12345(crearDocente(1001));
    $grupo->estudiantes()->attach($estudiante->id);

    expect(fn () => $grupo->registrarNota($estudiante, $nota))
        ->toThrow(InvalidArgumentException::class);

    expect(matriculaDe($grupo, $estudiante)->nota)->toBeNull();
})->with([-1, 10.5]);

test('CP-13 un estudiante no se matricula dos veces en el mismo grupo', function () {
    $grupo = crearGrupo01();
    $estudiante = crearEstudianteC12345(crearDocente(1001));
    $grupo->estudiantes()->attach($estudiante->id);

    expect(fn () => $grupo->estudiantes()->attach($estudiante->id))
        ->toThrow(QueryException::class);

    expect(EstudianteMatriculado::where('grupo_id', $grupo->id)
        ->where('estudiante_id', $estudiante->id)
        ->count())->toBe(1);
});