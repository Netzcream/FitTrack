<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('workout_id')
                ->constrained('workouts')
                ->onDelete('cascade');

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->onDelete('cascade');

            // Datos de ejecución
            $table->integer('sets_completed')->nullable();
            $table->json('reps_per_set')->nullable(); // [10, 10, 8, 8]
            $table->decimal('weight_used_kg', 8, 2)->nullable();
            $table->integer('duration_seconds')->nullable(); // Para ejercicios por tiempo
            $table->integer('rest_time_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Metadata flexible
            $table->json('meta')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['workout_id']);
            $table->index(['exercise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_exercises');
    }
};
