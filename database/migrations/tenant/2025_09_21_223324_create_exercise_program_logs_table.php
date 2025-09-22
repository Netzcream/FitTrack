<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_program_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_workout_id')->constrained('exercise_program_workouts')->cascadeOnDelete();

            $table->unsignedBigInteger('student_id'); // FK blanda (tenant users/students)
            $table->dateTime('performed_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedTinyInteger('rpe_session')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['program_workout_id', 'student_id', 'performed_at'], 'prg_log_idx');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_program_logs');
    }
};
