<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_plan_workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('exercise_plans')->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->unsignedInteger('day_index')->default(1);
            $table->unsignedInteger('week_index')->nullable();
            $table->string('focus')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedInteger('order')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_plan_workouts');
    }
};
