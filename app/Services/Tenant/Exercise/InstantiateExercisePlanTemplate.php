<?php

namespace App\Services\Tenant\Exercise;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Tenant\Exercise\{
    ExercisePlan,
    ExercisePlanWorkout,
    ExercisePlanBlock,
    ExercisePlanItem,
    ExercisePlanAssignment,
    ExercisePlanTemplate
};

class InstantiateExercisePlanTemplate
{
    /**
     * Clona una plantilla completa de entrenamiento hacia un plan real,
     * y opcionalmente lo asigna a un alumno.
     *
     * @param array{
     *   template_id:int,
     *   user_id:int,
     *   plan_name?:string,
     *   start_date?:string|\DateTimeInterface|null,
     *   student_id?:int|null,
     * } $data
     */
    public function handle(array $data): array
    {
        $template = ExercisePlanTemplate::with(['workouts.blocks.items'])
            ->findOrFail($data['template_id']);

        // Validaciones defensivas
        if ($template->workouts->isEmpty()) {
            abort(422, 'La plantilla no tiene entrenamientos definidos.');
        }
        if ($template->workouts->flatMap->blocks->flatMap->items->isEmpty()) {
            abort(422, 'La plantilla no contiene ejercicios.');
        }

        return DB::transaction(function () use ($template, $data) {

            /* ============================================================
             * 1️⃣ Crear plan base
             * ============================================================ */
            $plan = ExercisePlan::create([
                'source_template_id'      => $template->id,
                'name'                    => $data['plan_name'] ?? $template->name,
                'code'                    => strtoupper(Str::ulid()),
                'status'                  => isset($data['student_id']) ? 'active' : 'draft',
                'notes'                   => $template->description ?? null,
                'public_notes'            => null,
                'start_date'              => isset($data['start_date'])
                    ? Carbon::parse($data['start_date'])
                    : now(),
                'source_template_version' => $template->version ?? null,
                'created_by'              => $data['user_id'] ?? null,
                'updated_by'              => $data['user_id'] ?? null,
            ]);

            $workoutMap = [];
            $blockMap   = [];

            /* ============================================================
             * 2️⃣ Clonar workouts
             * ============================================================ */
            foreach ($template->workouts->sortBy('order') as $tw) {
                $workout = ExercisePlanWorkout::create([
                    'plan_id'    => $plan->id,
                    'name'       => $tw->name,
                    'day_index'  => $tw->day_index ?? 1,
                    'week_index' => $tw->week_index ?? 1,
                    'focus'      => $tw->meta['focus'] ?? null,
                    'notes'      => $tw->notes,
                    'order'      => $tw->order ?? 0,
                ]);
                $workoutMap[$tw->id] = $workout->id;
            }

            /* ============================================================
             * 3️⃣ Clonar bloques
             * ============================================================ */
            $validTypes = ['warmup', 'main', 'accessory', 'conditioning', 'cooldown', 'other'];

            foreach ($template->workouts as $tw) {
                foreach ($tw->blocks->sortBy('order') as $tb) {
                    $rawType = $tb->type ?: 'main';
                    $type = in_array($rawType, $validTypes, true) ? $rawType : 'main';

                    $block = ExercisePlanBlock::create([
                        'plan_workout_id' => $workoutMap[$tw->id],
                        'name'            => $tb->name,
                        'type'            => $type,
                        'is_circuit'      => (bool)($tb->meta['is_circuit'] ?? false),
                        'rounds'          => $tb->meta['rounds'] ?? null,
                        'notes'           => $tb->notes,
                        'order'           => $tb->order ?? 0,
                    ]);

                    $blockMap[$tb->id] = $block->id;
                }
            }

            //clonar items
            $itemRows = [];

            foreach ($workoutMap as $templateWorkoutId => $newWorkoutId) {
                $templateWorkout = $template->workouts->firstWhere('id', $templateWorkoutId);
                if (!$templateWorkout) continue;

                foreach ($templateWorkout->blocks as $tb) {
                    // Verificamos que el bloque haya sido mapeado correctamente
                    if (!isset($blockMap[$tb->id])) continue;

                    foreach ($tb->items->sortBy('order') as $ti) {
                        // Extraemos datos del JSON prescription (de la plantilla)
                        $prescription = $ti->prescription ?? [];
                        $sets = $prescription['sets'] ?? 3;
                        $reps = null;
                        $reps_min = null;
                        $reps_max = null;

                        if (isset($prescription['reps']) && is_array($prescription['reps'])) {
                            $reps = $prescription['reps'][0] ?? null;
                            $reps_min = min($prescription['reps']);
                            $reps_max = max($prescription['reps']);
                        }

                        $itemRows[] = [
                            'plan_block_id'     => $blockMap[$tb->id],
                            'exercise_id'       => $ti->exercise_id,
                            'order'             => $ti->order ?? 0,
                            'sets'              => $sets,
                            'reps'              => $reps,
                            'reps_min'          => $reps_min,
                            'reps_max'          => $reps_max,
                            'rest_sec'          => $ti->rest_seconds ?? 90,
                            'tempo'             => $ti->tempo,
                            'rir'               => null,
                            'load_prescription' => $ti->prescription
                                ? json_encode($ti->prescription)
                                : null,
                            'notes'             => $ti->notes,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ];
                    }
                }
            }

            // Inserción masiva sin duplicados
            foreach (array_chunk($itemRows, 500) as $chunk) {
                ExercisePlanItem::insert($chunk);
            }

            /* ============================================================
             * 5️⃣ Crear asignación (si hay alumno)
             * ============================================================ */
            $assignment = null;

            if (!empty($data['student_id'])) {
                // 🔸 Finalizar otras asignaciones activas del mismo alumno
                ExercisePlanAssignment::where('student_id', $data['student_id'])
                    ->where('is_active', true)
                    ->update([
                        'is_active' => false,
                        'status'    => 'finished',
                        'end_date'  => now(),
                        'updated_at' => now(),
                    ]);

                // 🔸 Crear nueva asignación activa
                $assignment = ExercisePlanAssignment::create([
                    'plan_id'    => $plan->id,
                    'student_id' => $data['student_id'],
                    'start_date' => $plan->start_date,
                    'end_date'   => null,
                    'is_active'  => true,
                    'status'     => 'active',
                ]);
            }

            return compact('plan', 'assignment');
        });
    }
}
