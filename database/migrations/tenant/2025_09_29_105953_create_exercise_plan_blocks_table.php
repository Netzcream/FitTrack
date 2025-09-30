<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_plan_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_workout_id')->constrained('exercise_plan_workouts')->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->enum('type', ['normal','superset','circuit','giantset'])->default('normal');
            $table->boolean('is_circuit')->default(false);
            $table->unsignedInteger('rounds')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedInteger('order')->default(0);

            $table->softDeletes();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_plan_blocks');
    }
};
