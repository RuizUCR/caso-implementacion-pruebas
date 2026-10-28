<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EstudianteMatriculado extends Pivot
{
    protected $table = 'estudiante_matriculado';

    public $incrementing = true;

    protected $fillable = ['grupo_id', 'estudiante_id', 'nota'];

    protected $casts = ['nota' => 'float'];
}
