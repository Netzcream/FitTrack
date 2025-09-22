<?php

namespace App\Livewire\Tenant\Exercises\ExerciseLevels;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\ExerciseLevel;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $id = null;
    public bool $editMode = false;

    // Campos del modelo ExerciseLevel
    public string $name = '';
    public string $code = '';
    public ?string $description = null;
    public string $status = ExerciseLevel::STATUS_DRAFT;

    // Navegación
    public bool $back = true;

    public function mount(?ExerciseLevel $exerciseLevel): void
    {
        if ($exerciseLevel && $exerciseLevel->exists) {
            $this->editMode   = true;
            $this->id         = (int) $exerciseLevel->id;
            $this->name       = (string) $exerciseLevel->name;
            $this->code       = (string) $exerciseLevel->code;
            $this->description= $exerciseLevel->description;
            $this->status     = (string) ($exerciseLevel->status ?? ExerciseLevel::STATUS_DRAFT);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required','string','max:100',
                $this->editMode
                    ? Rule::unique('exercise_levels', 'name')->ignore($this->id)
                    : Rule::unique('exercise_levels', 'name'),
            ],
            'code' => [
                'required','string','max:100',
                $this->editMode
                    ? Rule::unique('exercise_levels', 'code')->ignore($this->id)
                    : Rule::unique('exercise_levels', 'code'),
            ],
            'description' => ['nullable','string'],
            'status' => ['required', Rule::in([
                ExerciseLevel::STATUS_DRAFT,
                ExerciseLevel::STATUS_PUBLISHED,
                ExerciseLevel::STATUS_ARCHIVED,
            ])],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        /** @var ExerciseLevel $level */
        $level = $this->editMode
            ? ExerciseLevel::findOrFail($this->id)
            : new ExerciseLevel();

        $level->fill([
            'name'        => $validated['name'],
            'code'        => $validated['code'],
            'description' => $validated['description'] ?? null,
            'status'      => $validated['status'],
        ])->save();

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('exercise.exercise_level_updated'));
            $this->mount($level->fresh());
        } else {
            session()->flash('success', __('exercise.exercise_level_created'));
        }

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.exercise.exercise-levels.index'), navigate: true);
        }

        return $this->redirect(route('tenant.dashboard.exercise.exercise-levels.edit', $level), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.exercises.exercise-levels.form');
    }
}
