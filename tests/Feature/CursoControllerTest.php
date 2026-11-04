<?php

namespace Tests\Feature;

use App\Models\Curso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearCurso(string $sigla = 'ECCI-101'): Curso
    {
        return Curso::create([
            'sigla' => $sigla,
            'nombreCurso' => 'Programación I',
            'creditos' => 4,
        ]);
    }

    public function test_index_lista_los_cursos(): void
    {
        $this->crearCurso('ECCI-101');
        $this->crearCurso('ECCI-102');

        $this->getJson('/cursos')
            ->assertOk()
            ->assertJsonCount(2);
    }

    public function test_show_devuelve_un_curso(): void
    {
        $curso = $this->crearCurso();

        $this->getJson("/cursos/{$curso->id}")
            ->assertOk()
            ->assertJsonFragment(['sigla' => 'ECCI-101', 'creditos' => 4]);
    }

    public function test_store_crea_un_curso(): void
    {
        $this->postJson('/cursos', [
            'sigla' => 'ECCI-200',
            'nombreCurso' => 'Bases de Datos',
            'creditos' => 3,
        ])->assertCreated();

        $this->assertDatabaseHas('cursos', ['sigla' => 'ECCI-200']);
    }

    public function test_store_valida_los_datos(): void
    {
        $this->postJson('/cursos', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sigla', 'nombreCurso', 'creditos']);
    }

    public function test_store_rechaza_sigla_repetida(): void
    {
        $this->crearCurso('ECCI-101');

        $this->postJson('/cursos', [
            'sigla' => 'ECCI-101',
            'nombreCurso' => 'Otro curso',
            'creditos' => 2,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['sigla']);
    }
}