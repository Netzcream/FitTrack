<?php

namespace App\Livewire\Tenant\Exercises\MuscleGroups;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\MuscleGroup;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $id = null;
    public bool $editMode = false;

    public string $name = '';
    public string $code = '';
    public ?string $description = null;
    public string $status = MuscleGroup::STATUS_DRAFT;

    public bool $back = true;

    public function mount(?MuscleGroup $muscleGroup): void
    {
        if ($muscleGroup && $muscleGroup->exists) {
            $this->editMode    = true;
            $this->id          = (int) $muscleGroup->id;
            $this->name        = $muscleGroup->name;
            $this->code        = $muscleGroup->code;
            $this->description = $muscleGroup->description;
            $this->status      = $muscleGroup->status ?? MuscleGroup::STATUS_DRAFT;
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required','string','max:100',
                $this->editMode
                    ? Rule::unique('exercise_muscle_groups', 'name')->ignore($this->id)
                    : Rule::unique('exercise_muscle_groups', 'name'),
            ],
            'code' => [
                'required','string','max:100',
                $this->editMode
                    ? Rule::unique('exercise_muscle_groups', 'code')->ignore($this->id)
                    : Rule::unique('exercise_muscle_groups', 'code'),
            ],
            'description' => ['nullable','string'],
            'status' => ['required', Rule::in([
                MuscleGroup::STATUS_DRAFT,
                MuscleGroup::STATUS_PUBLISHED,
                MuscleGroup::STATUS_ARCHIVED,
            ])],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $group = $this->editMode
            ? MuscleGroup::findOrFail($this->id)
            : new MuscleGroup();

        $group->fill($validated)->save();

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('exercise.muscle_group_updated'));
            $this->mount($group->fresh());
        } else {
            session()->flash('success', __('exercise.muscle_group_created'));
        }

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.exercise.muscle-groups.index'), navigate: true);
        }

        return $this->redirect(route('tenant.dashboard.exercise.muscle-groups.edit', $group), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.exercises.muscle-groups.form');
    }
}
