<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grupo extends Model
{
    public const MIN_DOCENTES = 1;
    public const MAX_DOCENTES = 2;
    public const MIN_ESTUDIANTES = 15;

    protected $fillable = ['curso_id', 'numeroCiclo', 'ano', 'numeroGrupo', 'abierto'];

    protected $casts = ['abierto' => 'boolean'];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'grupo_docente');
    }

    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'estudiantes_matriculados')
            ->using(EstudianteMatriculado::class)
            ->withPivot('nota');
    }

    // RN-02: un grupo admite como máximo 2 docentes
    public function asignarDocente(Docente $docente): void
    {
        if ($this->docentes()->count() >= self::MAX_DOCENTES) {
            throw new DomainException('Un grupo admite como máximo ' . self::MAX_DOCENTES . ' docentes.');
        }

        $this->docentes()->attach($docente->id);
    }

    // RN-02 (mínimo 1 docente) y RN-05 (mínimo 15 estudiantes)
    public function puedeAbrirse(): bool
    {
        return $this->docentes()->count() >= self::MIN_DOCENTES
            && $this->estudiantes()->count() >= self::MIN_ESTUDIANTES;
    }

    public function abrir(): void
    {
        if (! $this->puedeAbrirse()) {
            throw new DomainException('El grupo no cumple los requisitos para abrirse.');
        }

        $this->update(['abierto' => true]);
    }
}