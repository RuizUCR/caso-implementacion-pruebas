<?php

use App\Http\Controllers\CursoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cursos', [CursoController::class, 'index']);
Route::get('/cursos/{curso}', [CursoController::class, 'show']);
Route::post('/cursos', [CursoController::class, 'store']);