<?php

namespace App\Livewire\Tenant\TrainingPlan;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\Exercise;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?TrainingPlan $plan = null;

    public string $name = '';
    public string $description = '';
    public string $goal = '';
    public string $duration = '';
    public bool $is_active = true;

    public bool $editMode = false;
    public bool $back = false;

    public string $exerciseSearch = '';
    public array $selectedExercises = [];
    public $availableExercises = [];

    /* -------------------- Mount -------------------- */
    public function mount(?TrainingPlan $trainingPlan): void
    {
        if ($trainingPlan && $trainingPlan->exists) {
            $this->plan = $trainingPlan;
            $this->name = $trainingPlan->name;
            $this->description = $trainingPlan->description ?? '';
            $this->goal = $trainingPlan->goal ?? '';
            $this->duration = (string) ($trainingPlan->duration ?? '');
            $this->is_active = (bool) $trainingPlan->is_active;
            $this->editMode = true;

            $this->selectedExercises = $trainingPlan->exercises()
                ->orderBy('plan_exercise.day')
                ->orderBy('plan_exercise.order')
                ->get()
                ->map(fn($e) => [
                    'id' => $e->id,
                    'uuid' => $e->uuid,
                    'name' => $e->name,
                    'category' => $e->category,
                    'image' => $e->getFirstMediaUrl('images', 'thumb'),
                    'day' => $e->pivot->day,
                    'order' => $e->pivot->order,
                    'detail' => $e->pivot->detail,
                    'notes' => $e->pivot->notes,
                ])
                ->toArray();
        }
    }

    /* -------------------- Reglas -------------------- */
    protected function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'goal'        => ['nullable', 'string', 'max:255'],
            'duration'    => ['nullable'],
            'is_active'   => ['boolean'],
            'selectedExercises.*.day' => ['nullable', 'integer', 'min:1', 'max:7'],
            'selectedExercises.*.detail' => ['nullable', 'string', 'max:50'],
            'selectedExercises.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /* -------------------- Búsqueda ejercicios -------------------- */
    public function updatedExerciseSearch(): void
    {
        if (strlen($this->exerciseSearch) < 2) {
            $this->availableExercises = [];
            return;
        }

        $excludeIds = collect($this->selectedExercises)->pluck('id')->all();
        $this->availableExercises = Exercise::active()
            ->search($this->exerciseSearch)
            ->whereNotIn('id', $excludeIds)
            ->take(5)
            ->get(['id', 'uuid', 'name','category'])
            ->toArray();
    }

    /* -------------------- Agregar/Quitar ejercicios -------------------- */
    public function addExercise(int $id): void
    {
        $exercise = Exercise::find($id);
        if (!$exercise) return;

        $this->selectedExercises[] = [
            'id' => $exercise->id,
            'uuid' => $exercise->uuid,
            'name' => $exercise->name,
            'category' => $exercise->category,
            'image' => $exercise->getFirstMediaUrl('images', 'thumb'),
            'day' => 1,
            'order' => count($this->selectedExercises) + 1,
            'detail' => '',
            'notes' => '',
        ];

        $this->exerciseSearch = '';
        $this->availableExercises = [];
    }

    public function removeExercise(int $index): void
    {
        array_splice($this->selectedExercises, $index, 1);
        $this->reorderExercises();
    }

    public function moveUp(int $index): void
    {
        if ($index === 0) return;
        [$this->selectedExercises[$index - 1], $this->selectedExercises[$index]] =
            [$this->selectedExercises[$index], $this->selectedExercises[$index - 1]];
        $this->reorderExercises();
    }

    public function moveDown(int $index): void
    {
        if ($index === count($this->selectedExercises) - 1) return;
        [$this->selectedExercises[$index + 1], $this->selectedExercises[$index]] =
            [$this->selectedExercises[$index], $this->selectedExercises[$index + 1]];
        $this->reorderExercises();
    }

    protected function reorderExercises(): void
    {
        foreach ($this->selectedExercises as $i => &$ex) {
            $ex['order'] = $i + 1;
        }
    }

    public function save()
    {
        $data = $this->validate();

        $plan = $this->editMode && $this->plan
            ? $this->plan
            : new TrainingPlan();

        $plan->fill($data);
        $plan->save();

        // Sincronizar ejercicios
        $pivotData = [];
        foreach ($this->selectedExercises as $ex) {
            $pivotData[$ex['id']] = [
                'day' => $ex['day'],
                'order' => $ex['order'],
                'detail' => $ex['detail'],
                'notes' => $ex['notes'],
            ];
        }
        $plan->exercises()->sync($pivotData);

        // Actualizar estado local (re-render)
        $this->plan = $plan;


        // Recargar relaciones desde base de datos
        $this->selectedExercises = $plan->exercises()
            ->orderBy('plan_exercise.day')
            ->orderBy('plan_exercise.order')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'uuid' => $e->uuid,
                'name' => $e->name,
                'category' => $e->category,
                'image' => $e->getFirstMediaUrl('images', 'thumb'),
                'day' => $e->pivot->day,
                'order' => $e->pivot->order,
                'detail' => $e->pivot->detail,
                'notes' => $e->pivot->notes,
            ])
            ->toArray();

        // Feedback visual
        $this->dispatch('saved');
        session()->flash('success', __('training_plans.saved'));

        // Si seleccionó "Volver al listado", redirigir
        if ($this->back) {
            return redirect()->route('tenant.dashboard.training-plans.index');
        }

        if (!$this->editMode) {
            return redirect()->route('tenant.dashboard.training-plans.edit', $plan->uuid);
        }
    }

    public function clearSearch(): void
    {
        $this->exerciseSearch = '';
        $this->availableExercises = [];
    }



    public function render()
    {
        return view('livewire.tenant.training-plan.form');
    }
}
