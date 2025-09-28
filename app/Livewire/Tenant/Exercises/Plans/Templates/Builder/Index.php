<?php

namespace App\Livewire\Tenant\Exercises\Plans\Templates\Builder;

use Livewire\Component;
use Livewire\Attributes\Layout;

use App\Models\Tenant\Exercise\ExercisePlanTemplate;
use App\Models\Tenant\Exercise\ExercisePlanTemplateWorkout as TplWorkout;
use App\Models\Tenant\Exercise\ExercisePlanTemplateBlock as TplBlock;
use App\Models\Tenant\Exercise\ExercisePlanTemplateItem as TplItem;
use App\Models\Tenant\Exercise\Exercise;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    public ExercisePlanTemplate $template;

    // Scaffold inicial
    public int $weeks_count = 4;
    public int $days_per_week = 3;

    public ?int $selected_week = null;


    public string $editorMode = 'add';     // 'add' | 'edit'
    public bool $editorReady = false;
    public ?int $editorWorkoutId = null;
    public ?int $editorItemId = null;
    public ?string $editorBlockType = 'main';
    public array $editorForm = [
        'exercise_id'     => null,
        'display_name'    => null,
        'modality'        => 'reps',
        'sets'            => 3,


        'reps'            => [],
        'time_seconds_arr'    => [],
        'distance_meters_arr' => [],
        'calories_arr'        => [],
        'target_rpe_arr'      => [],
        'load_arr'            => [],
        'tempo_arr'           => [],


        'time_seconds'    => null,
        'distance_meters' => null,
        'calories'        => null,
        'target_rpe'      => null,
        'load'            => null,
        'tempo'           => null,

        'rest_seconds'    => 90,
        'rpe'             => null,
        'notes'           => null,
    ];
    public string $editorQuery = '';
    public array $editorResults = [];



    public array $exerciseQuery = [];     // [workoutId => 'sentadilla']
    public array $exerciseResults = [];   // [workoutId => [[id,name,default_modality], ...]]
    public array $chosen = [];            // [workoutId => ['id','name','modality']]
    public array $target_block = [];      // [workoutId => 'main'|'warmup'|'cooldown']

    // Prescripción por día
    public array $sets = [];
    public array $reps_text = [];         // <- CSV p/ reps dinámicas (ej "10,12,10")
    public array $time_seconds = [];
    public array $distance_meters = [];
    public array $calories = [];
    public array $rpe = [];
    public array $load = [];
    public array $tempo = [];
    public array $rest_seconds = [];
    public array $notes = [];

    /** Edición por ÍTEM (en modal) */
    public ?int $editingItemId = null;
    public array $edit = [];              // [itemId => payload]
    public array $editQuery = [];         // buscador por ítem
    public array $editResults = [];       // resultados por ítem
    public array $editBlockType = [];     // [itemId => 'warmup'|'main'|'cooldown']
    public array $edit_reps_text = [];    // <- CSV para reps dinámicas al editar

    public function mount(ExercisePlanTemplate $template): void
    {
        $this->template = $template->load([
            'workouts' => fn($q) => $q->orderBy('week_index')->orderBy('day_index')->orderBy('order'),
            'workouts.blocks' => fn($q) => $q->orderBy('order'),
            'workouts.blocks.items' => fn($q) => $q->orderBy('order'),
        ]);
        $this->selected_week = $this->template->workouts->first()->week_index ?? 1;
    }


    protected function fitToSets(array $vals, int $n, $default = 10): array
    {
        $n   = max(1, min(6, (int)$n));
        $vals = array_map(fn($v) => is_numeric($v) ? (0 + $v) : $v, $vals);
        $len = count($vals);

        if ($len === $n) return array_values($vals);
        if ($len <  $n) {
            $last = $len ? end($vals) : $default;
            return array_values(array_merge($vals, array_fill(0, $n - $len, $last)));
        }
        return array_slice($vals, 0, $n);
    }
    public function openAddModal(int $workoutId): void
    {
        $this->resetEditor();
        $this->editorMode      = 'add';
        $this->editorWorkoutId = $workoutId;
        $this->editorBlockType = 'main';
        $this->editorForm['sets']     = max(1, min(6, (int)($this->editorForm['sets'] ?? 3)));
        $this->editorForm['modality'] = $this->editorForm['modality'] ?? 'reps';
        $this->updatedEditorFormSets($this->editorForm['sets']);
        $this->editorReady = true;
    }


    public function openEditModal(int $itemId): void
    {
        $this->resetEditor();
        $this->editorMode   = 'edit';
        $this->editorItemId = $itemId;

        $item = TplItem::with(['block.workout', 'exercise'])->findOrFail($itemId);
        $pr   = $item->prescription ?? [];
        $sets = (int)($pr['sets'] ?? 3);
        $mod  = $pr['modality'] ?? 'reps';

        // helpers: toma escalar o array y lo convierte a array tamaño "sets"
        $toArr = function ($val, int $n, $default) {
            $arr = is_array($val) ? $val : ($val !== null ? [$val] : []);
            return $this->fitToSets($arr, $n, $default);
        };

        $this->editorWorkoutId = $item->block->workout_id;
        $this->editorBlockType = $item->block->type;

        $this->editorForm = [
            'exercise_id'     => $item->exercise_id,
            'display_name'    => $item->display_name ?? ($item->exercise->name ?? null),
            'modality'        => $mod,
            'sets'            => $sets,

            'reps'                => $toArr($pr['reps'] ?? null, $sets, 10),
            'time_seconds_arr'    => $toArr($pr['time_seconds'] ?? null, $sets, 30),
            'distance_meters_arr' => $toArr($pr['distance_meters'] ?? null, $sets, 100),
            'calories_arr'        => $toArr($pr['calories'] ?? null, $sets, 10),
            'target_rpe_arr'      => $toArr($pr['target_rpe'] ?? null, $sets, 7),
            'load_arr'            => $toArr($pr['load'] ?? null, $sets, 20),
            'tempo_arr'           => $toArr($pr['tempo'] ?? null, $sets, '3010'),

            'rest_seconds'    => $item->rest_seconds,
            'rpe'             => $item->rpe,
            'notes'           => $item->notes,
            'time_seconds'    => $pr['time_seconds'] ?? null,
            'distance_meters' => $pr['distance_meters'] ?? null,
            'calories'        => $pr['calories'] ?? null,
            'target_rpe'      => $pr['target_rpe'] ?? null,
            'load'            => $pr['load'] ?? null,
            'tempo'           => $item->tempo,
        ];

        $this->editorReady = true;
    }



    public function updatedEditorFormSets($value): void
    {
        $n = max(1, min(6, (int)$value));
        $this->editorForm['sets']               = $n;
        $this->editorForm['reps']               = $this->fitToSets($this->editorForm['reps'] ?? [], $n, 10);
        $this->editorForm['time_seconds_arr']   = $this->fitToSets($this->editorForm['time_seconds_arr'] ?? [], $n, 30);
        $this->editorForm['distance_meters_arr'] = $this->fitToSets($this->editorForm['distance_meters_arr'] ?? [], $n, 100);
        $this->editorForm['calories_arr']       = $this->fitToSets($this->editorForm['calories_arr'] ?? [], $n, 10);
        $this->editorForm['target_rpe_arr']     = $this->fitToSets($this->editorForm['target_rpe_arr'] ?? [], $n, 7);
        $this->editorForm['load_arr']           = $this->fitToSets($this->editorForm['load_arr'] ?? [], $n, 20);
        $this->editorForm['tempo_arr']          = $this->fitToSets($this->editorForm['tempo_arr'] ?? [], $n, '3010');
    }

    public function updatedEditorFormModality($value): void
    {
        $n = (int)($this->editorForm['sets'] ?? 3);
        if ($value === 'reps') {
            $this->editorForm['reps'] = $this->fitToSets($this->editorForm['reps'] ?? [], $n, 10);
            return;
        }
        $this->updatedEditorFormSets($n);
    }

    protected function fitRepsToSets(array $reps, int $n): array
    {
        $n = max(0, min(50, (int)$n));
        $reps = array_map(fn($v) => (int)max(0, $v), $reps); // saneo
        $len = count($reps);

        if ($len === $n) return array_values($reps);

        if ($len < $n) {
            $last = $len ? (int)end($reps) : 10;
            return array_values(array_merge($reps, array_fill(0, $n - $len, $last)));
        }
        // $len > $n
        return array_slice($reps, 0, $n);
    }

    public function searchExercisesUnified(): void
    {
        $q = trim($this->editorQuery);
        if ($q === '') {
            $this->editorResults = [];
            return;
        }

        $this->editorResults = Exercise::query()
            ->where(fn($qq) => $qq->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
            ->orderBy('name')->limit(10)
            ->get(['id', 'name', 'default_modality'])
            ->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'default_modality' => $e->default_modality])
            ->all();
    }

    public function chooseExerciseUnified(int $exerciseId): void
    {
        $ex = Exercise::findOrFail($exerciseId);
        $this->editorForm['exercise_id']  = $ex->id;
        $this->editorForm['display_name'] = $ex->name;
        if (!$this->editorForm['modality']) {
            $this->editorForm['modality'] = $ex->default_modality ?? 'reps';
        }
        $this->editorQuery = '';
        $this->editorResults = [];
    }

    public function saveEditor(): void
    {
        // 1) guardrail: no hay ejercicio seleccionado
        if (!$this->editorForm['exercise_id']) {
            $this->dispatch('toast', type: 'warning', message: 'Elegí un ejercicio.');
            return;
        }


        $pres = $this->buildPrescriptionFromEditor();

        if ($this->editorMode === 'add') {
            // 3) Crear un item nuevo
            $block = TplBlock::where('workout_id', $this->editorWorkoutId)
                ->where('type', $this->editorBlockType ?? 'main')->firstOrFail();
            $order = (int) TplItem::where('block_id', $block->id)->max('order') + 1;

            TplItem::create([
                'block_id'      => $block->id,
                'exercise_id'   => $this->editorForm['exercise_id'],
                'display_name'  => $this->editorForm['display_name'],
                'order'         => $order,
                'prescription'  => $pres,                       // ← incluye 'reps' como array
                'tempo'         => $this->editorForm['tempo'],
                'rest_seconds'  => $this->editorForm['rest_seconds'],
                'rpe'           => $this->editorForm['rpe'],
                'external_load' => in_array($this->editorForm['modality'], ['load_only', 'reps']),
                'notes'         => $this->editorForm['notes'],
            ]);

            $this->dispatch('toast', type: 'success', message: 'Ejercicio agregado.');
        } else {
            // 4) Editar un item existente (y mover de bloque si cambió)
            $item = TplItem::with('block')->findOrFail($this->editorItemId);

            $newType = $this->editorBlockType ?? $item->block->type;
            if ($newType !== $item->block->type) {
                $newBlock = TplBlock::firstOrCreate(
                    ['workout_id' => $item->block->workout_id, 'type' => $newType],
                    ['name' => ucfirst($newType), 'order' => (int) TplBlock::where('workout_id', $item->block->workout_id)->max('order') + 1]
                );
                $item->block_id = $newBlock->id;
            }

            // 5) Persistimos cambios
            $item->exercise_id  = $this->editorForm['exercise_id'];
            $item->display_name = $this->editorForm['display_name'];
            $item->prescription = $pres;                      // ← incluye 'reps' como array
            $item->tempo        = $this->editorForm['tempo'];
            $item->rest_seconds = $this->editorForm['rest_seconds'];
            $item->rpe          = $this->editorForm['rpe'];
            $item->notes        = $this->editorForm['notes'];
            $item->save();

            $this->dispatch('toast', type: 'success', message: 'Ítem actualizado.');
        }

        // 6) Refrescamos, cerramos y limpiamos el editor SIEMPRE
        $this->refreshTemplate();
        $this->dispatch('modal-close', name: 'exercise-editor');
        $this->resetEditor();
    }


    protected function buildPrescriptionFromEditor(): array
    {
        $m    = $this->editorForm['modality'] ?? 'reps';
        $sets = (int)($this->editorForm['sets'] ?? 0);
        $base = ['scheme' => 'straight_sets', 'modality' => $m, 'sets' => $sets];

        $fit = fn($key, $default) =>
        $this->fitToSets($this->editorForm[$key] ?? [], $sets, $default);

        return match ($m) {
            'reps'       => $base + ['reps'            => $fit('reps', 10)],
            'time'       => $base + ['time_seconds'    => $fit('time_seconds_arr', 30)],
            'distance'   => $base + ['distance_meters' => $fit('distance_meters_arr', 100)],
            'calories'   => $base + ['calories'        => $fit('calories_arr', 10)],
            'rpe'        => $base + ['target_rpe'      => $fit('target_rpe_arr', 7)],
            'load_only'  => $base + ['load'            => $fit('load_arr', 20)],
            'tempo_only' => $base + ['tempo'           => $fit('tempo_arr', '3010')],
            default      => $base,
        };
    }


    public function resetEditor(): void
    {
        $this->editorMode = 'add';
        $this->editorReady = false;
        $this->editorWorkoutId = null;
        $this->editorItemId = null;
        $this->editorBlockType = 'main';
        $this->editorQuery = '';
        $this->editorResults = [];
        $this->editorForm = [
            'exercise_id'  => null,
            'display_name' => null,
            'modality'     => 'reps',
            'sets'         => 3,

            'reps'                => [],
            'time_seconds_arr'    => [],
            'distance_meters_arr' => [],
            'calories_arr'        => [],
            'target_rpe_arr'      => [],
            'load_arr'            => [],
            'tempo_arr'           => [],

            'time_seconds'    => null,
            'distance_meters' => null,
            'calories'        => null,
            'target_rpe'      => null,
            'load'            => null,
            'tempo'           => null,

            'rest_seconds' => 90,
            'rpe'          => null,
            'notes'        => null,
        ];
    }

    public function scaffoldWeeks(): void
    {
        $this->validate([
            'weeks_count'   => ['required', 'integer', 'min:1', 'max:52'],
            'days_per_week' => ['required', 'integer', 'min:1', 'max:7'],
        ]);

        for ($w = 1; $w <= $this->weeks_count; $w++) {
            for ($d = 1; $d <= $this->days_per_week; $d++) {
                $exists = TplWorkout::where('template_id', $this->template->id)
                    ->where('week_index', $w)->where('day_index', $d)->exists();

                if (!$exists) {
                    $workout = TplWorkout::create([
                        'template_id' => $this->template->id,
                        'week_index'  => $w,
                        'day_index'   => $d,
                        'name'        => "Semana $w · Día $d",
                        'order'       => (int) TplWorkout::where('template_id', $this->template->id)->max('order') + 1,
                    ]);
                    $this->ensureDefaultBlocks($workout->id);
                }
            }
        }

        $this->refreshTemplate();
        $this->dispatch('toast', type: 'success', message: 'Estructura generada.');
    }

    public function addDay(int $weekIndex): void
    {
        $maxDay = (int) TplWorkout::where('template_id', $this->template->id)
            ->where('week_index', $weekIndex)->max('day_index');
        $day = $maxDay ? $maxDay + 1 : 1;

        $workout = TplWorkout::create([
            'template_id' => $this->template->id,
            'week_index'  => $weekIndex,
            'day_index'   => $day,
            'name'        => "Semana $weekIndex · Día $day",
            'order'       => (int) TplWorkout::where('template_id', $this->template->id)->max('order') + 1,
        ]);

        $this->ensureDefaultBlocks($workout->id);
        $this->refreshTemplate();
    }

    public function removeDay(int $workoutId): void
    {
        TplWorkout::whereKey($workoutId)->delete();

        unset(
            $this->exerciseQuery[$workoutId],
            $this->exerciseResults[$workoutId],
            $this->chosen[$workoutId],
            $this->target_block[$workoutId],
            $this->sets[$workoutId],
            $this->reps_text[$workoutId],
            $this->time_seconds[$workoutId],
            $this->distance_meters[$workoutId],
            $this->calories[$workoutId],
            $this->rpe[$workoutId],
            $this->load[$workoutId],
            $this->tempo[$workoutId],
            $this->rest_seconds[$workoutId],
            $this->notes[$workoutId],
        );

        $this->refreshTemplate();
    }

    protected function ensureDefaultBlocks(int $workoutId): void
    {
        foreach ([['warmup', 'Calentamiento'], ['main', 'Principal'], ['cooldown', 'Enfriamiento']] as $i => [$type, $name]) {
            $exists = TplBlock::where('workout_id', $workoutId)->where('type', $type)->exists();
            if (!$exists) {
                TplBlock::create([
                    'workout_id' => $workoutId,
                    'type'       => $type,
                    'name'       => $name,
                    'order'      => $i + 1,
                ]);
            }
        }
    }

    /** Buscar / elegir ejercicios (por día) */
    public function searchExercises(int $workoutId): void
    {
        $q = trim($this->exerciseQuery[$workoutId] ?? '');
        if ($q === '') {
            $this->exerciseResults[$workoutId] = [];
            return;
        }

        $this->exerciseResults[$workoutId] = Exercise::query()
            ->where(fn($qq) => $qq->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
            ->orderBy('name')->limit(10)
            ->get(['id', 'name', 'default_modality'])
            ->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'default_modality' => $e->default_modality])
            ->all();
    }

    public function chooseExercise(int $workoutId, int $exerciseId): void
    {
        $ex = Exercise::whereKey($exerciseId)->firstOrFail();
        $this->chosen[$workoutId] = ['id' => $ex->id, 'name' => $ex->name, 'modality' => $ex->default_modality];

        // Defaults mínimos
        $this->sets[$workoutId]       = $this->sets[$workoutId]       ?? 3;
        $this->reps_text[$workoutId]  = $this->reps_text[$workoutId]  ?? '10';
        $this->rest_seconds[$workoutId] = $this->rest_seconds[$workoutId] ?? 90;
        $this->target_block[$workoutId]  = $this->target_block[$workoutId]  ?? 'main';

        $this->exerciseQuery[$workoutId] = '';
        $this->exerciseResults[$workoutId] = [];
    }

    public function addItemToDay(int $workoutId): void
    {
        $chosen = $this->chosen[$workoutId] ?? null;
        if (!$chosen) {
            $this->dispatch('toast', type: 'warning', message: 'Elegí un ejercicio primero.');
            return;
        }

        $blockType = $this->target_block[$workoutId] ?? 'main';
        $block = TplBlock::where('workout_id', $workoutId)->where('type', $blockType)->firstOrFail();

        $order = (int) TplItem::where('block_id', $block->id)->max('order') + 1;
        $prescription = $this->makePrescriptionPayload($workoutId, $chosen['modality']);

        TplItem::create([
            'block_id'      => $block->id,
            'exercise_id'   => $chosen['id'],
            'display_name'  => $chosen['name'],
            'order'         => $order,
            'prescription'  => $prescription,
            'tempo'         => $this->tempo[$workoutId] ?? null,
            'rest_seconds'  => $this->rest_seconds[$workoutId] ?? null,
            'rpe'           => $this->rpe[$workoutId] ?? null,
            'external_load' => in_array($chosen['modality'], ['load_only', 'reps']),
            'notes'         => $this->notes[$workoutId] ?? null,
        ]);

        // Limpiar UI de ese día
        unset(
            $this->chosen[$workoutId],
            $this->sets[$workoutId],
            $this->reps_text[$workoutId],
            $this->time_seconds[$workoutId],
            $this->distance_meters[$workoutId],
            $this->calories[$workoutId],
            $this->rpe[$workoutId],
            $this->load[$workoutId],
            $this->tempo[$workoutId],
            $this->rest_seconds[$workoutId],
            $this->notes[$workoutId],
        );

        $this->refreshTemplate();
        $this->dispatch('toast', type: 'success', message: 'Ejercicio agregado.');
        $this->dispatch('item-added', workoutId: $workoutId); // cierre del modal
    }

    protected function makePrescriptionPayload(int $workoutId, ?string $modality): array
    {
        $base = ['scheme' => 'straight_sets', 'modality' => $modality, 'sets' => $this->sets[$workoutId] ?? null];

        // helper parse reps CSV a int[]
        $parseReps = function (?string $csv): ?array {
            if (!$csv) return null;
            $parts = array_filter(array_map('trim', preg_split('/[,\s]+/', $csv)));
            if (!$parts) return null;
            return array_map(fn($n) => (int)$n, $parts);
        };

        return match ($modality) {
            'time'      => $base + ['time_seconds' => $this->time_seconds[$workoutId] ?? null],
            'distance'  => $base + ['distance_meters' => $this->distance_meters[$workoutId] ?? null],
            'calories'  => $base + ['calories' => $this->calories[$workoutId] ?? null],
            'rpe'       => $base + ['target_rpe' => $this->rpe[$workoutId] ?? null],
            'load_only' => $base + ['load' => $this->load[$workoutId] ?? null],
            'tempo_only' => $base + ['tempo' => $this->tempo[$workoutId] ?? null],
            default     => $base + ['reps' => $parseReps($this->reps_text[$workoutId] ?? '10')],
        };
    }

    public function beginEditItem(int $itemId): void
    {
        $item = TplItem::with(['block.workout', 'exercise'])->findOrFail($itemId);
        $pr = $item->prescription ?? [];

        $this->editingItemId = $itemId;
        $this->edit[$itemId] = [
            'exercise_id'     => $item->exercise_id,
            'display_name'    => $item->display_name ?? ($item->exercise->name ?? null),
            'modality'        => $pr['modality'] ?? 'reps',
            'sets'            => $pr['sets'] ?? null,
            'time_seconds'    => $pr['time_seconds'] ?? null,
            'distance_meters' => $pr['distance_meters'] ?? null,
            'calories'        => $pr['calories'] ?? null,
            'target_rpe'      => $pr['target_rpe'] ?? null,
            'load'            => $pr['load'] ?? null,
            'tempo'           => $item->tempo,
            'rest_seconds'    => $item->rest_seconds,
            'rpe'             => $item->rpe,
            'notes'           => $item->notes,
        ];

        // reps dinámicas -> CSV para el input
        $this->edit_reps_text[$itemId] = isset($pr['reps']) && is_array($pr['reps'])
            ? implode(',', $pr['reps'])
            : '';

        $this->editBlockType[$itemId] = $item->block->type;
        $this->editQuery[$itemId] = '';
        $this->editResults[$itemId] = [];
        // NOTA: ya NO abrimos modal por evento; lo abre el trigger.
    }

    public function cancelEditItem(): void
    {
        $id = $this->editingItemId;
        $this->editingItemId = null;
        unset(
            $this->edit[$id],
            $this->editQuery[$id],
            $this->editResults[$id],
            $this->editBlockType[$id],
            $this->edit_reps_text[$id]
        );
        // Cierre lo hace <flux:modal.close> del botón Cancelar
    }

    public function searchExercisesForItem(int $itemId): void
    {
        $q = trim($this->editQuery[$itemId] ?? '');
        if ($q === '') {
            $this->editResults[$itemId] = [];
            return;
        }

        $this->editResults[$itemId] = Exercise::query()
            ->where(fn($qq) => $qq->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
            ->orderBy('name')->limit(10)
            ->get(['id', 'name', 'default_modality'])
            ->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'default_modality' => $e->default_modality])
            ->all();
    }

    public function chooseExerciseForItem(int $itemId, int $exerciseId): void
    {
        $ex = Exercise::findOrFail($exerciseId);
        $this->edit[$itemId]['exercise_id']  = $ex->id;
        $this->edit[$itemId]['display_name'] = $ex->name;
        $this->edit[$itemId]['modality']     = $ex->default_modality;

        $this->edit[$itemId]['sets'] = $this->edit[$itemId]['sets'] ?? 3;
        $this->edit_reps_text[$itemId] = $this->edit_reps_text[$itemId] ?? '10';

        $this->editQuery[$itemId] = '';
        $this->editResults[$itemId] = [];
    }

    public function saveEditItem(int $itemId): void
    {
        $item = TplItem::with(['block.workout'])->findOrFail($itemId);
        $wId  = $item->block->workout_id;
        $data = $this->edit[$itemId] ?? [];

        // mover de bloque si cambió
        $type = $this->editBlockType[$itemId] ?? $item->block->type;
        if ($type !== $item->block->type) {
            $newBlock = TplBlock::where('workout_id', $wId)->where('type', $type)->first();
            if (!$newBlock) {
                $newBlock = TplBlock::create([
                    'workout_id' => $wId,
                    'type'       => $type,
                    'name'       => ucfirst($type),
                    'order'      => (int) TplBlock::where('workout_id', $wId)->max('order') + 1,
                ]);
            }
            $item->block_id = $newBlock->id;
        }

        // reps CSV -> array
        $repsCsv = $this->edit_reps_text[$itemId] ?? '';
        $repsArr = null;
        if ($repsCsv !== '') {
            $parts = array_filter(array_map('trim', preg_split('/[,\s]+/', $repsCsv)));
            if ($parts) $repsArr = array_map(fn($n) => (int)$n, $parts);
        }

        // prescription
        $mod = $data['modality'] ?? 'reps';
        $pres = ['scheme' => 'straight_sets', 'modality' => $mod, 'sets' => $data['sets'] ?? null];
        $pres = match ($mod) {
            'time'       => $pres + ['time_seconds' => $data['time_seconds'] ?? null],
            'distance'   => $pres + ['distance_meters' => $data['distance_meters'] ?? null],
            'calories'   => $pres + ['calories' => $data['calories'] ?? null],
            'rpe'        => $pres + ['target_rpe' => $data['target_rpe'] ?? null],
            'load_only'  => $pres + ['load' => $data['load'] ?? null],
            'tempo_only' => $pres + ['tempo' => $data['tempo'] ?? null],
            default      => $pres + ['reps' => $repsArr],
        };

        $item->exercise_id   = $data['exercise_id'] ?? $item->exercise_id;
        $item->display_name  = $data['display_name'] ?? $item->display_name;
        $item->prescription  = $pres;
        $item->tempo         = $data['tempo'] ?? $item->tempo;
        $item->rest_seconds  = $data['rest_seconds'] ?? $item->rest_seconds;
        $item->rpe           = $data['rpe'] ?? $item->rpe;
        $item->notes         = $data['notes'] ?? $item->notes;
        $item->save();

        $this->cancelEditItem();
        $this->refreshTemplate();
        $this->dispatch('toast', type: 'success', message: 'Ítem actualizado.');

        // Cierre programático del modal de este ítem (opción A)
        $this->dispatch('modal-close', name: "edit-item-{$itemId}");
    }

    public function removeItem(int $itemId): void
    {
        TplItem::whereKey($itemId)->delete();
        $this->refreshTemplate();
        $this->dispatch('toast', type: 'success', message: 'Ítem eliminado.');
    }

    protected function refreshTemplate(): void
    {
        $this->template->refresh()->load([
            'workouts' => fn($q) => $q->orderBy('week_index')->orderBy('day_index')->orderBy('order'),
            'workouts.blocks' => fn($q) => $q->orderBy('order'),
            'workouts.blocks.items' => fn($q) => $q->orderBy('order'),
        ]);
    }

    public function render()
    {
        return view('livewire.tenant.exercises.plans.templates.builder.index', [
            'template' => $this->template,
        ]);
    }
}
