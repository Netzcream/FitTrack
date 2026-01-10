<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, migrar datos existentes de workout_exercises a workouts.exercises_data
        if (Schema::hasTable('workout_exercises')) {
            $workouts = DB::table('workouts')->get();

            foreach ($workouts as $workout) {
                $exercises = DB::table('workout_exercises')
                    ->where('workout_id', $workout->id)
                    ->get();

                if ($exercises->isNotEmpty()) {
                    $exercisesData = [];

                    foreach ($exercises as $idx => $ex) {
                        $exerciseInfo = DB::table('exercises')->find($ex->exercise_id);

                        $exercisesData[] = [
                            'exercise_id' => $ex->exercise_id,
                            'exercise_name' => $exerciseInfo->name ?? 'N/A',
                            'sets_completed' => $ex->sets_completed,
                            'reps_per_set' => json_decode($ex->reps_per_set, true) ?? [],
                            'weight_used_kg' => $ex->weight_used_kg,
                            'duration_seconds' => $ex->duration_seconds,
                            'rest_time_seconds' => $ex->rest_time_seconds,
                            'notes' => $ex->notes,
                            'completed_at' => $ex->completed_at,
                            'order' => $idx + 1,
                        ];
                    }

                    DB::table('workouts')
                        ->where('id', $workout->id)
                        ->update(['exercises_data' => json_encode($exercisesData)]);
                }
            }
        }

        // Luego eliminar la tabla
        Schema::dropIfExists('workout_exercises');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recrear tabla workout_exercises
        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_id')->constrained('workouts')->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained('exercises')->onDelete('cascade');
            $table->integer('sets_completed')->nullable();
            $table->json('reps_per_set')->nullable();
            $table->decimal('weight_used_kg', 8, 2)->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->integer('rest_time_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['workout_id']);
            $table->index(['exercise_id']);
        });

        // Restaurar datos desde exercises_data
        $workouts = DB::table('workouts')->whereNotNull('exercises_data')->get();

        foreach ($workouts as $workout) {
            $exercisesData = json_decode($workout->exercises_data, true) ?? [];

            foreach ($exercisesData as $ex) {
                DB::table('workout_exercises')->insert([
                    'workout_id' => $workout->id,
                    'exercise_id' => $ex['exercise_id'] ?? null,
                    'sets_completed' => $ex['sets_completed'] ?? null,
                    'reps_per_set' => json_encode($ex['reps_per_set'] ?? []),
                    'weight_used_kg' => $ex['weight_used_kg'] ?? null,
                    'duration_seconds' => $ex['duration_seconds'] ?? null,
                    'rest_time_seconds' => $ex['rest_time_seconds'] ?? null,
                    'notes' => $ex['notes'] ?? null,
                    'completed_at' => $ex['completed_at'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
