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
        Schema::create('exercise_completion_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            // Relaciones
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->foreignId('workout_id')->nullable()->constrained('workouts')->nullOnDelete();

            // Fecha de completado (clave para anti-farming)
            $table->date('completed_date')->comment('Fecha del día en que se completó');

            // XP otorgado
            $table->unsignedSmallInteger('xp_earned')->comment('XP ganado por este ejercicio (10, 15 o 20)');

            // Dificultad del ejercicio al momento de completarlo (snapshot)
            $table->string('exercise_level')->comment('Nivel del ejercicio: beginner, intermediate, advanced');

            // Metadatos del ejercicio (opcional, para auditoría)
            $table->json('exercise_snapshot')->nullable()->comment('Snapshot del ejercicio al momento de completar');

            $table->timestamps();

            // ÍNDICE ÚNICO CRÍTICO: Anti-farming
            // Un alumno NO puede completar el mismo ejercicio más de una vez por día
            $table->unique(['student_id', 'exercise_id', 'completed_date'], 'unique_student_exercise_per_day');

            // Índices para queries
            $table->index(['student_id', 'completed_date']);
            $table->index('workout_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_completion_logs');
    }
};
