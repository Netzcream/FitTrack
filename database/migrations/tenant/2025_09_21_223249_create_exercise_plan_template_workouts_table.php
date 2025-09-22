<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_plan_template_workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('exercise_plan_templates')->cascadeOnDelete();
            $table->unsignedSmallInteger('week_index')->default(1); // 1..N
            $table->unsignedTinyInteger('day_index')->default(1);  // 1..7
            $table->string('name')->nullable();                    // p.ej. "Día A"
            $table->text('notes')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['template_id', 'week_index', 'day_index', 'order'], 'tpl_wk_day_ord_idx');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_plan_template_workouts');
    }
};
