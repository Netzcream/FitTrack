<?php

namespace App\Livewire\Tenant\TrainingPlan;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Tenant\TrainingPlan;
use App\Models\Tenant\Exercise;
use App\Models\Tenant\Student;
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

    /** Alumno asignado (ID numérico) o null */
    public ?int $student_id = null;

    /** Fechas de vigencia (solo si hay student_id) */
    public ?string $assigned_from = null;   // 'Y-m-d'
    public ?string $assigned_until = null;  // 'Y-m-d'

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

            $this->student_id    = $trainingPlan->student_id;
            $this->assigned_from = optional($trainingPlan->assigned_from)?->format('Y-m-d');
            $this->assigned_until = optional($trainingPlan->assigned_until)?->format('Y-m-d');

            // Cargar exercises_data
            $exercisesData = $trainingPlan->exercises_data ?? [];
            $this->selectedExercises = [];

            if (!empty($exercisesData)) {
                $exerciseIds = collect($exercisesData)->pluck('exercise_id')->toArray();
                $exercises = Exercise::whereIn('id', $exerciseIds)->get()->keyBy('id');

                foreach ($exercisesData as $item) {
                    $exercise = $exercises->get($item['exercise_id']);
                    if ($exercise) {
                        $this->selectedExercises[] = [
                            'id' => $exercise->id,
                            'uuid' => $exercise->uuid,
                            'name' => $exercise->name,
                            'category' => $exercise->category,
                            'image' => $exercise->getFirstMediaUrl('images', 'thumb'),
                            'day' => $item['day'] ?? 1,
                            'order' => $item['order'] ?? 1,
                            'detail' => $item['detail'] ?? '',
                            'notes' => $item['notes'] ?? '',
                        ];
                    }
                }
            }
        } else {
            // Si viene ?student=<uuid> desde la vista del alumno, convertir a ID
            if ($uuid = request()->query('student')) {
                $student = Student::where('uuid', $uuid)->first();
                if ($student) {
                    $this->student_id = $student->id;
                    // Defaults de vigencia cuando creamos un plan asignado
                    $this->assigned_from  = now()->toDateString();
                    $this->assigned_until = now()->addMonth()->toDateString();
                }
            }
        }
    }

    /* -------------------- Reglas -------------------- */
    protected function rules(): array
    {
        $rules = [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'goal'        => ['nullable', 'string', 'max:255'],
            'duration'    => ['nullable'],
            'is_active'   => ['boolean'],

            'selectedExercises.*.day'    => ['nullable', 'integer', 'min:1', 'max:7'],
            'selectedExercises.*.detail' => ['nullable', 'string', 'max:50'],
            'selectedExercises.*.notes'  => ['nullable', 'string', 'max:255'],
        ];

        // Validar fechas solo si el plan está asignado a un alumno
        if ($this->student_id) {
            $rules['assigned_from']  = ['nullable', 'date'];
            $rules['assigned_until'] = ['nullable', 'date', 'after_or_equal:assigned_from'];
        }

        return $rules;
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
            ->get(['id', 'uuid', 'name', 'category'])
            ->toArray();
    }

    /* -------------------- Agregar/Quitar ejercicios -------------------- */
    #[On('exercise-created')]
    public function onExerciseCreated($exerciseId)
    {
        // Agregar automáticamente el ejercicio recién creado
        $this->addExercise($exerciseId);

        // Limpiar búsqueda
        $this->exerciseSearch = '';
        $this->availableExercises = [];
    }

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

    /* -------------------- Guardar -------------------- */
    public function save(): void
    {
        $this->validate();

        // Crear o editar
        $plan = $this->editMode && $this->plan ? $this->plan : new TrainingPlan();

        $plan->fill([
            'name'        => $this->name,
            'description' => $this->description,
            'goal'        => $this->goal,
            'duration'    => $this->duration,
            'is_active'   => $this->is_active,
            'student_id'  => $this->student_id, // null para planes generales
        ]);


        // 🔹 Validar solapamiento de fechas para el alumno
        if ($this->student_id && $this->is_active) {
            $graceDays = 2;

            $from  = $plan->assigned_from ?? now();
            $until = $plan->assigned_until ?? now()->addDays(30);

            $overlap = TrainingPlan::where('student_id', $this->student_id)
                ->where('id', '!=', $plan->id)
                ->where('is_active', true)
                ->where(function ($q) use ($from, $until, $graceDays) {
                    $q->whereBetween('assigned_from', [$from->copy()->subDays($graceDays), $until->copy()->addDays($graceDays)])
                        ->orWhereBetween('assigned_until', [$from->copy()->subDays($graceDays), $until->copy()->addDays($graceDays)])
                        ->orWhere(function ($qq) use ($from, $until, $graceDays) {
                            $qq->where('assigned_from', '<=', $from->copy()->subDays($graceDays))
                                ->where('assigned_until', '>=', $until->copy()->addDays($graceDays));
                        });
                })
                ->exists();

            if ($overlap) {
                $this->addError('assigned_from', __('training_plans.overlap_error'));
                return;
            }
        }


        $plan->save();
        $plan->refresh();

        // Guardar exercises_data como JSON
        $exercisesData = [];

        foreach ($this->selectedExercises as $ex) {
            $exercisesData[] = [
                'exercise_id' => $ex['id'],
                'day'    => $ex['day'] ?? 1,
                'order'  => $ex['order'] ?? 1,
                'detail' => $ex['detail'] ?? '',
                'notes'  => $ex['notes'] ?? '',
            ];
        }

        $plan->exercises_data = $exercisesData;
        $plan->save();

        // Refrescar para UI
        $this->plan = $plan;

        // Feedback
        $this->dispatch('saved');
        session()->flash('success', __('training_plans.saved'));

        // Navegación
        if ($this->back) {
            if ($this->student_id && $plan->student) {
                $this->redirectRoute('tenant.dashboard.students.training-plans', [
                    'student' => $plan->student->uuid,
                ]);
            } else {
                $this->redirectRoute('tenant.dashboard.training-plans.index');
            }
            return;
        }

        if (!$this->editMode) {
            if ($this->student_id && $plan->student) {
                $this->redirectRoute('tenant.dashboard.students.training-plans', [
                    'student' => $plan->student->uuid,
                ]);
            } else {
                $this->redirectRoute('tenant.dashboard.training-plans.edit', $plan->uuid);
            }
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
