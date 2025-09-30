<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_plan_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('exercise_plans')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);

            $table->unique(['plan_id','student_id','is_active'], 'uniq_active_assignment');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['student_id','is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_plan_assignments');
    }
};
