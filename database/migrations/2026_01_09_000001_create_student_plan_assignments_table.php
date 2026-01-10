<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_plan_assignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('training_plan_id')->constrained('training_plans');

            // Snapshot + metadata
            $table->string('name');
            $table->json('meta')->nullable(); // includes version, origin, parent_uuid, etc.
            $table->json('exercises_snapshot')->nullable();

            // Lifecycle
            $table->boolean('is_active')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();

            // Optional per-student overrides for small adjustments
            $table->json('overrides')->nullable();

            $table->timestamps();

            // Constraint: Only one active assignment per student.
            // MySQL doesn't support partial unique index, so use a generated column trick.
            $table->unsignedBigInteger('active_student_id')->nullable()->storedAs('IF(is_active, student_id, NULL)');
            $table->unique('active_student_id', 'uniq_active_assignment_per_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_plan_assignments');
    }
};
