<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Relaciones principales
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('plan_workout_id')
                ->nullable()
                ->constrained('exercise_plan_workouts')
                ->nullOnDelete();

            $table->foreignId('plan_block_id')
                ->nullable()
                ->constrained('exercise_plan_blocks')
                ->nullOnDelete();

            // Información de la sesión
            $table->date('scheduled_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();

            // Métricas y feedback
            $table->tinyInteger('session_rpe')->nullable(); // esfuerzo subjetivo (1–10)
            $table->json('meta')->nullable(); // flexibilidad futura
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sessions');
    }
};
