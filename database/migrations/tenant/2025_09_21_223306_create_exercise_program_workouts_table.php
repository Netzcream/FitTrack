<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_program_workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('exercise_programs')->cascadeOnDelete();

            // snapshot + mapeo a la plantilla
            $table->foreignId('template_workout_id')->nullable()->constrained('exercise_plan_template_workouts')->nullOnDelete();

            $table->unsignedSmallInteger('week_index')->default(1);
            $table->unsignedTinyInteger('day_index')->default(1);
            $table->string('name')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['program_id', 'week_index', 'day_index', 'order'], 'prg_wk_day_ord_idx');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_program_workouts');
    }
};
