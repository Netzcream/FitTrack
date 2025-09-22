<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_plan_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained('exercise_plan_template_blocks')->cascadeOnDelete();
            $table->foreignId('exercise_id')->nullable()->constrained('exercise_exercises')->nullOnDelete();
            $table->string('display_name')->nullable(); // snapshot opcional del nombre
            $table->json('snapshot_exercise')->nullable(); // snapshot opcional del ejercicio
            $table->unsignedInteger('order')->default(0);
            $table->json('prescription')->nullable(); // sets/reps/tiempo/%1RM/EMOM/AMRAP
            $table->string('tempo')->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->unsignedTinyInteger('rpe')->nullable();
            $table->boolean('external_load')->default(false);
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['block_id', 'order']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('exercise_plan_template_items');
    }
};
