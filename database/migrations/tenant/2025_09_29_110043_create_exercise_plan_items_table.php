<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercise_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_block_id')->constrained('exercise_plan_blocks')->cascadeOnDelete();

            $table->foreignId('exercise_id')->constrained('exercise_exercises')->cascadeOnDelete();

            $table->unsignedInteger('order')->default(0);

            // Prescripción
            $table->unsignedInteger('sets')->nullable();
            $table->unsignedInteger('reps')->nullable();
            $table->unsignedInteger('reps_min')->nullable();
            $table->unsignedInteger('reps_max')->nullable();
            $table->unsignedInteger('rest_sec')->nullable();
            $table->string('tempo')->nullable();
            $table->unsignedTinyInteger('rir')->nullable();
            $table->json('load_prescription')->nullable();

            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_plan_items');
    }
};
