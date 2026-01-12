<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('student_plan_assignment_id')->constrained('student_plan_assignments')->cascadeOnDelete();

            // Plan day and cycle tracking
            $table->unsignedInteger('plan_day')->comment('Day number in the plan (1..N)');
            $table->unsignedInteger('sequence_index')->comment('Total completed workouts before this one');
            $table->unsignedInteger('cycle_index')->comment('Which cycle in the plan (1, 2, etc.)');

            // Timeline
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();

            // Status and feedback
            $table->string('status')->default('pending')->comment('pending, in_progress, completed, skipped');
            $table->unsignedTinyInteger('rating')->nullable()->comment('1-5 scale workout rating');
            $table->text('notes')->nullable();

            // Workout data snapshot
            $table->json('exercises_data')->nullable()->comment('Exercises snapshot with sets, reps, weight, time');

            // Meta fields (fatigue, RPE, pain, etc.)
            $table->json('meta')->nullable()->comment('Quick survey: fatigue, RPE, pain, mood, notes');

            // Timestamps
            $table->timestamps();

            // Indexes for common queries
            $table->index(['student_id', 'status']);
            $table->index(['student_plan_assignment_id', 'completed_at']);
            $table->index(['student_id', 'completed_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workouts');
    }
};
