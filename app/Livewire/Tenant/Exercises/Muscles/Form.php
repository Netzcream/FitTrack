<?php

namespace App\Livewire\Tenant\Exercises\Muscles;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\Muscle;
use App\Models\Tenant\Exercise\MuscleGroup;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $id = null;
    public bool $editMode = false;

    public ?int $muscle_group_id = null;
    public string $name = '';
    public string $code = '';
    public ?string $description = null;
    public string $status = Muscle::STATUS_DRAFT;

    public bool $back = true;

    public function mount(?Muscle $muscle): void
    {
        if ($muscle && $muscle->exists) {
            $this->editMode       = true;
            $this->id             = (int) $muscle->id;
            $this->muscle_group_id= $muscle->muscle_group_id;
            $this->name           = (string) $muscle->name;
            $this->code           = (string) $muscle->code;
            $this->description    = $muscle->description;
            $this->status         = (string) ($muscle->status ?? Muscle::STATUS_DRAFT);
        }
    }

    public function rules(): array
    {
        return [
            'muscle_group_id' => ['nullable', 'integer', 'exists:exercise_muscle_groups,id'],
            'name' => [
                'required','string','max:100',
                $this->editMode
                    ? Rule::unique('exercise_muscles', 'name')->ignore($this->id)
                    : Rule::unique('exercise_muscles', 'name'),
            ],
            'code' => [
                'required','string','max:100',
                $this->editMode
                    ? Rule::unique('exercise_muscles', 'code')->ignore($this->id)
                    : Rule::unique('exercise_muscles', 'code'),
            ],
            'description' => ['nullable','string'],
            'status' => ['required', Rule::in([
                Muscle::STATUS_DRAFT,
                Muscle::STATUS_PUBLISHED,
                Muscle::STATUS_ARCHIVED,
            ])],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        /** @var Muscle $muscle */
        $muscle = $this->editMode
            ? Muscle::findOrFail($this->id)
            : new Muscle();

        $muscle->fill([
            'muscle_group_id' => $validated['muscle_group_id'] ?? null,
            'name'            => $validated['name'],
            'code'            => $validated['code'],
            'description'     => $validated['description'] ?? null,
            'status'          => $validated['status'],
        ])->save();

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('exercise.muscle_updated'));
            $this->mount($muscle->fresh());
        } else {
            session()->flash('success', __('exercise.muscle_created'));
        }

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.exercise.muscles.index'), navigate: true);
        }

        return $this->redirect(route('tenant.dashboard.exercise.muscles.edit', $muscle), navigate: true);
    }

    public function render()
    {
        $groups = MuscleGroup::orderBy('name')->get(['id','name']);
        return view('livewire.tenant.exercises.muscles.form', compact('groups'));
    }
}
