<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Solo agregar la columna si no existe
        if (!Schema::hasColumn('training_plans', 'exercises_data')) {
            Schema::table('training_plans', function (Blueprint $table) {
                $table->json('exercises_data')->nullable()->after('duration');
            });
        }

        // Migrar datos existentes de plan_exercise a exercises_data (solo si la tabla existe)
        if (Schema::hasTable('plan_exercise')) {
            $plans = DB::table('training_plans')->get();

            foreach ($plans as $plan) {
                // Solo migrar si exercises_data está vacío/null
                if (empty($plan->exercises_data)) {
                    $exercises = DB::table('plan_exercise')
                        ->where('training_plan_id', $plan->id)
                        ->orderBy('day')
                        ->orderBy('order')
                        ->get();

                    if ($exercises->isNotEmpty()) {
                        $exercisesData = $exercises->map(function ($pivot) {
                            return [
                                'exercise_id' => $pivot->exercise_id,
                                'day' => $pivot->day,
                                'order' => $pivot->order,
                                'detail' => $pivot->detail,
                                'notes' => $pivot->notes,
                            ];
                        })->toArray();

                        DB::table('training_plans')
                            ->where('id', $plan->id)
                            ->update(['exercises_data' => json_encode($exercisesData)]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Restaurar datos a plan_exercise
        $plans = DB::table('training_plans')->whereNotNull('exercises_data')->get();

        foreach ($plans as $plan) {
            $exercisesData = json_decode($plan->exercises_data, true);

            if (!empty($exercisesData)) {
                foreach ($exercisesData as $exercise) {
                    DB::table('plan_exercise')->insert([
                        'training_plan_id' => $plan->id,
                        'exercise_id' => $exercise['exercise_id'],
                        'day' => $exercise['day'] ?? 1,
                        'order' => $exercise['order'] ?? 1,
                        'detail' => $exercise['detail'] ?? null,
                        'notes' => $exercise['notes'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        Schema::table('training_plans', function (Blueprint $table) {
            $table->dropColumn('exercises_data');
        });
    }
};
