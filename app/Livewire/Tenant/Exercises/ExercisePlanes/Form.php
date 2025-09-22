<?php

namespace App\Livewire\Tenant\Exercises\ExercisePlanes;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\ExercisePlane;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $id = null;
    public bool $editMode = false;

    public string $name = '';
    public string $code = '';
    public ?string $description = null;
    public string $status = ExercisePlane::STATUS_DRAFT;

    public bool $back = true;

    public function mount(?ExercisePlane $exercisePlane): void
    {
        if ($exercisePlane && $exercisePlane->exists) {
            $this->editMode    = true;
            $this->id          = (int) $exercisePlane->id;
            $this->name        = $exercisePlane->name;
            $this->code        = $exercisePlane->code;
            $this->description = $exercisePlane->description;
            $this->status      = $exercisePlane->status ?? ExercisePlane::STATUS_DRAFT;
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required','string','max:100',
                $this->editMode
                    ? Rule::unique('exercise_planes', 'name')->ignore($this->id)
                    : Rule::unique('exercise_planes', 'name'),
            ],
            'code' => [
                'required','string','max:100',
                $this->editMode
                    ? Rule::unique('exercise_planes', 'code')->ignore($this->id)
                    : Rule::unique('exercise_planes', 'code'),
            ],
            'description' => ['nullable','string'],
            'status' => ['required', Rule::in([
                ExercisePlane::STATUS_DRAFT,
                ExercisePlane::STATUS_PUBLISHED,
                ExercisePlane::STATUS_ARCHIVED,
            ])],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $plane = $this->editMode
            ? ExercisePlane::findOrFail($this->id)
            : new ExercisePlane();

        $plane->fill($validated)->save();

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('exercise.exercise_plane_updated'));
            $this->mount($plane->fresh());
        } else {
            session()->flash('success', __('exercise.exercise_plane_created'));
        }

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.exercise.exercise-planes.index'), navigate: true);
        }

        return $this->redirect(route('tenant.dashboard.exercise.exercise-planes.edit', $plane), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.exercises.exercise-planes.form');
    }
}
