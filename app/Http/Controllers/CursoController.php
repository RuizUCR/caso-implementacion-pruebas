<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Curso::all());
    }

    public function show(Curso $curso): JsonResponse
    {
        return response()->json($curso->load('grupos'));
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'sigla' => 'required|string|unique:cursos,sigla',
            'nombreCurso' => 'required|string',
            'creditos' => 'required|integer|min:1',
        ]);

        $curso = Curso::create($datos);

        return response()->json($curso, 201);
    }
}