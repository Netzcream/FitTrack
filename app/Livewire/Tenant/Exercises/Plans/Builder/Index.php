<?php

namespace App\Livewire\Tenant\Exercises\Plans\Builder;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\ExercisePlan;
use App\Models\Tenant\Exercise\ExercisePlanWorkout;
use App\Models\Tenant\Exercise\ExercisePlanBlock;
use App\Models\Tenant\Exercise\ExercisePlanItem;
use App\Models\Tenant\Exercise\Exercise;
use App\Enums\Exercise\BlockType;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    public ExercisePlan $plan;
    public ?int $selected_week = null;

    // Editor modal
    public string $editorMode = 'add'; // add|edit
    public bool $editorReady = false;
    public ?int $editorWorkoutId = null;
    public ?int $editorItemId = null;
    public ?string $editorBlockType = 'main';
    public string $editorQuery = '';
    public array $editorResults = [];

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
        'rest_seconds'        => 90,
        'rpe'                 => null,
        'notes'               => null,
    ];

    public function mount(ExercisePlan $plan): void
    {
        $this->plan = $plan->load([
            'workouts.blocks.items.exercise'
        ]);
        $this->selected_week = $this->plan->workouts->first()->week_index ?? 1;
    }

    protected function fitToSets(array $vals, int $n, $default = 10): array
    {
        $n = max(1, min(6, $n));
        $vals = array_map(fn($v) => is_numeric($v) ? 0 + $v : $v, $vals);
        if (count($vals) >= $n) return array_slice($vals, 0, $n);
        $last = count($vals) ? end($vals) : $default;
        return array_merge($vals, array_fill(0, $n - count($vals), $last));
    }

    // ---------- Modal actions ----------

    public function openAddModal(int $workoutId): void
    {
        $this->resetEditor();
        $this->editorMode = 'add';
        $this->editorWorkoutId = $workoutId;
        $this->editorReady = true;
    }

    public function openEditModal(int $itemId): void
    {
        $this->resetEditor();
        $this->editorMode = 'edit';
        $this->editorItemId = $itemId;

        $item = ExercisePlanItem::with(['block.workout', 'exercise'])->findOrFail($itemId);
        $pr = $item->prescription ?? [];
        $sets = (int)($pr['sets'] ?? 3);
        $mod = $pr['modality'] ?? 'reps';
        $fit = fn($key, $default) => $this->fitToSets($pr[$key] ?? [], $sets, $default);

        $this->editorWorkoutId = $item->block->plan_workout_id;
        $this->editorBlockType = $item->block->type instanceof \App\Enums\Exercise\BlockType
            ? $item->block->type->value
            : (string)$item->block->type;
        $this->editorForm = [
            'exercise_id' => $item->exercise_id,
            'display_name' => $item->display_name ?? ($item->exercise->name ?? null),
            'modality' => $mod,
            'sets' => $sets,
            'reps' => $fit('reps', 10),
            'rest_seconds' => $item->rest_seconds,
            'rpe' => $item->rpe,
            'notes' => $item->notes,
        ];
        $this->editorReady = true;
    }

    public function searchExercisesUnified(): void
    {
        $q = trim($this->editorQuery);
        if ($q === '') {
            $this->editorResults = [];
            return;
        }
        $this->editorResults = Exercise::query()
            ->where(fn($qq) => $qq->where('name', 'like', "%$q%")->orWhere('code', 'like', "%$q%"))
            ->orderBy('name')->limit(10)
            ->get(['id', 'name', 'default_modality'])
            ->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'default_modality' => $e->default_modality])
            ->all();
    }

    public function chooseExerciseUnified(int $id): void
    {
        $ex = Exercise::findOrFail($id);
        $this->editorForm['exercise_id'] = $ex->id;
        $this->editorForm['display_name'] = $ex->name;
        $this->editorForm['modality'] = $ex->default_modality ?? 'reps';
        $this->editorQuery = '';
        $this->editorResults = [];
    }

    public function saveEditor(): void
    {
        if (!$this->editorForm['exercise_id']) {
            $this->dispatch('toast', type: 'warning', message: 'Elegí un ejercicio.');
            return;
        }
        $type = $this->editorBlockType ? BlockType::from($this->editorBlockType) : BlockType::Main;
        $pres = $this->buildPrescription();

        if ($this->editorMode === 'add') {
            $block = ExercisePlanBlock::firstOrCreate(
                ['plan_workout_id' => $this->editorWorkoutId, 'type' => $type],
                ['name' => $type->label()]
            );
            $order = (int)(ExercisePlanItem::where('plan_block_id', $block->id)->max('order') ?? 0) + 1;
            ExercisePlanItem::create([
                'plan_block_id' => $block->id,
                'exercise_id' => $this->editorForm['exercise_id'],
                'display_name' => $this->editorForm['display_name'],
                'order' => $order,
                'prescription' => $pres,
                'rest_seconds' => $this->editorForm['rest_seconds'],
                'rpe' => $this->editorForm['rpe'],
                'notes' => $this->editorForm['notes'],
            ]);
            $this->dispatch('toast', type: 'success', message: 'Ejercicio agregado.');
        } else {
            $item = ExercisePlanItem::findOrFail($this->editorItemId);
            $item->update([
                'exercise_id' => $this->editorForm['exercise_id'],
                'display_name' => $this->editorForm['display_name'],
                'prescription' => $pres,
                'rest_seconds' => $this->editorForm['rest_seconds'],
                'rpe' => $this->editorForm['rpe'],
                'notes' => $this->editorForm['notes'],
            ]);
            $this->dispatch('toast', type: 'success', message: 'Ejercicio actualizado.');
        }

        $this->resetEditor();
        $this->plan->refresh()->load(['workouts.blocks.items.exercise']);
        $this->dispatch('modal-close', name: 'exercise-editor');
    }

    protected function buildPrescription(): array
    {
        $m = $this->editorForm['modality'] ?? 'reps';
        $sets = (int)($this->editorForm['sets'] ?? 3);
        $fit = fn($key, $default) => $this->fitToSets($this->editorForm[$key] ?? [], $sets, $default);
        return match ($m) {
            'reps' => ['scheme' => 'straight_sets', 'modality' => $m, 'sets' => $sets, 'reps' => $fit('reps', 10)],
            'time' => ['scheme' => 'straight_sets', 'modality' => $m, 'sets' => $sets, 'time_seconds' => $fit('time_seconds_arr', 30)],
            default => ['scheme' => 'straight_sets', 'modality' => $m, 'sets' => $sets],
        };
    }

    public function removeItem(int $id): void
    {
        ExercisePlanItem::whereKey($id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Ejercicio eliminado.');
        $this->plan->refresh()->load(['workouts.blocks.items.exercise']);
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
            'exercise_id' => null,
            'display_name' => null,
            'modality' => 'reps',
            'sets' => 3,
            'reps' => [],
            'rest_seconds' => 90,
            'rpe' => null,
            'notes' => null,
        ];
    }

    public function render()
    {
        return view('livewire.tenant.exercises.plans.builder.index', [
            'plan' => $this->plan->load(['workouts.blocks.items.exercise']),
        ]);
    }
}
