<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_program_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_workout_id')->constrained('exercise_program_workouts')->cascadeOnDelete();

            $table->foreignId('template_block_id')->nullable()->constrained('exercise_plan_template_blocks')->nullOnDelete();

            $table->enum('type', ['warmup', 'main', 'accessory', 'conditioning', 'cooldown', 'other'])->default('main');
            $table->string('name')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['program_workout_id', 'order']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_program_blocks');
    }
};
