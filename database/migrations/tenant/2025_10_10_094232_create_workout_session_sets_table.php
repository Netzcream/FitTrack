<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workout_session_sets', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('workout_session_id')
                ->constrained('workout_sessions')
                ->cascadeOnDelete();

            $table->foreignId('plan_item_id')
                ->nullable()
                ->constrained('exercise_plan_items')
                ->nullOnDelete();

            $table->unsignedSmallInteger('set_number')->default(1);

            // Prescripción del plan
            $table->decimal('target_load', 6, 2)->nullable();
            $table->unsignedSmallInteger('target_reps')->nullable();
            $table->unsignedSmallInteger('target_rest_sec')->nullable();

            // Ejecución real
            $table->decimal('actual_load', 6, 2)->nullable();
            $table->unsignedSmallInteger('actual_reps')->nullable();
            $table->unsignedSmallInteger('actual_rest_sec')->nullable();

            // Feedback del set
            $table->tinyInteger('rpe')->nullable(); // 1–10
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['workout_session_id', 'set_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_session_sets');
    }
};
