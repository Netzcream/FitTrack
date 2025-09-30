<?php

namespace App\Services\Tenant\Exercise;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Tenant\Exercise\ExercisePlan;
use App\Models\Tenant\Exercise\ExercisePlanWorkout;
use App\Models\Tenant\Exercise\ExercisePlanBlock;
use App\Models\Tenant\Exercise\ExercisePlanItem;
use App\Models\Tenant\Exercise\ExercisePlanAssignment;

use App\Models\Tenant\Exercise\ExercisePlanTemplate;

class InstantiateExercisePlanTemplate
{
    /**
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
        // El scope multitenant limita por tenant automáticamente.
        $template = ExercisePlanTemplate::with(['workouts.blocks.items'])
            ->findOrFail($data['template_id']);

        // Validaciones defensivas
        if ($template->workouts->isEmpty()) {
            abort(422, 'La plantilla no tiene workouts.');
        }
        if ($template->workouts->flatMap->blocks->flatMap->items->isEmpty()) {
            abort(422, 'La plantilla no tiene ítems para instanciar.');
        }

        return DB::transaction(function () use ($template, $data) {

            // 1) Crear plan
            $plan = ExercisePlan::create([
                'source_template_id'      => $template->id,
                'name'                    => $data['plan_name'] ?? ($template->name ?: 'Plan'),
                'code'                    => strtoupper(Str::ulid()),
                'status'                  => isset($data['student_id']) ? 'active' : 'draft',
                'notes'                   => $template->notes ?? null,
                'public_notes'            => null,
                'start_date'              => isset($data['start_date']) ? Carbon::parse($data['start_date']) : null,
                'source_template_version' => $template->version ?? null,
                'created_by'              => $data['user_id'] ?? null,
                'updated_by'              => $data['user_id'] ?? null,
            ]);

            // Mapas de IDs
            $workoutMap = [];
            $blockMap   = [];

            // 2) Clonar WORKOUTS (crear uno a uno, mapear id al vuelo)
            // Orden estable sugerido (order asc, id asc)
            $workoutsSorted = $template->workouts->sortBy(fn($w) => [($w->order ?? 0), $w->id]);

            foreach ($workoutsSorted as $tw) {
                $w = ExercisePlanWorkout::create([
                    'plan_id'    => $plan->id,
                    'name'       => $tw->name,
                    'day_index'  => $tw->day_index ?? 1,
                    'week_index' => $tw->week_index,
                    'focus'      => $tw->focus,
                    'notes'      => $tw->notes,
                    'order'      => $tw->order ?? 0,
                ]);
                $workoutMap[$tw->id] = $w->id;
            }

            // 3) Clonar BLOCKS (crear uno a uno, mapear id al vuelo)
            // Si tu ENUM NO admite warmup/main/cooldown, dejamos sólo estos 4:
            $validTypes = ['warmup', 'main', 'accessory', 'conditioning', 'cooldown', 'other'];



            foreach ($workoutsSorted as $tw) {
                if (!isset($workoutMap[$tw->id])) {
                    throw new \RuntimeException("Workout map missing for template workout {$tw->id}");
                }

                $blocksSorted = $tw->blocks->sortBy(fn($b) => [($b->order ?? 0), $b->id]);

                foreach ($blocksSorted as $tb) {
                    $rawType = $tb->type ?: 'main';
                    $type = in_array($rawType, $validTypes, true) ? $rawType : 'main';
                    $b = ExercisePlanBlock::create([
                        'plan_workout_id' => $workoutMap[$tw->id],
                        'name'            => $tb->name,
                        'type'            => $type,
                        'is_circuit'      => (bool)($tb->is_circuit ?? ($rawType === 'circuit')),
                        'rounds'          => $tb->rounds,
                        'notes'           => $tb->notes,
                        'order'           => $tb->order ?? 0,
                    ]);

                    $blockMap[$tb->id] = $b->id;
                }
            }

            // 4) Clonar ITEMS (bulk insert con plan_block_id ya resuelto)
            $itemRows = [];
            foreach ($template->workouts as $tw) {
                foreach ($tw->blocks as $tb) {
                    if (!isset($blockMap[$tb->id])) {
                        throw new \RuntimeException("Block map missing for template block {$tb->id}");
                    }
                    foreach ($tb->items->sortBy(fn($i) => [($i->order ?? 0), $i->id]) as $ti) {
                        $itemRows[] = [
                            'plan_block_id'     => $blockMap[$tb->id],
                            'exercise_id'       => $ti->exercise_id,
                            'order'             => $ti->order ?? 0,
                            'sets'              => $ti->sets,
                            'reps'              => $ti->reps,
                            'reps_min'          => $ti->reps_min,
                            'reps_max'          => $ti->reps_max,
                            'rest_sec'          => $ti->rest_sec,
                            'tempo'             => $ti->tempo,
                            'rir'               => $ti->rir,
                            'load_prescription' => $ti->load_prescription,
                            'notes'             => $ti->notes,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ];
                    }
                }
            }
            foreach (array_chunk($itemRows, 1000) as $chunk) {
                ExercisePlanItem::insert($chunk);
            }

            // 5) Assignment opcional
            $assignment = null;
            if (!empty($data['student_id'])) {
                $assignment = ExercisePlanAssignment::create([
                    'plan_id'    => $plan->id,
                    'student_id' => $data['student_id'],
                    'start_date' => $plan->start_date ?? Carbon::today(),
                    'end_date'   => null,
                    'is_active'  => true,
                ]);
            }

            return compact('plan', 'assignment');
        });
    }
}
