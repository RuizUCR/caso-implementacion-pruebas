<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estudiante_matriculado', function (Blueprint $table) {
    $table->id();
    $table->foreignId('grupo_id')->constrained('grupos');
    $table->foreignId('estudiante_id')->constrained('estudiantes');
    $table->float('nota')->nullable();
    $table->unique(['grupo_id', 'estudiante_id']);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiante_matriculado');
    }
};
