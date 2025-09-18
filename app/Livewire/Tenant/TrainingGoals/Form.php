<?php

namespace App\Livewire\Tenant\TrainingGoals;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\TrainingGoal;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $id = null;
    public bool $editMode = false;

    public string $name = '';
    public string $code = '';
    public bool $is_active = true;

    public bool $back = true;

    public function mount(?TrainingGoal $trainingGoal): void
    {
        if ($trainingGoal && $trainingGoal->exists) {
            $this->editMode = true;
            $this->id       = (int) $trainingGoal->id;
            $this->name     = (string) $trainingGoal->name;
            $this->code     = (string) $trainingGoal->code;
            $this->is_active = (bool) $trainingGoal->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100'],
            'code'      => [
                'required',
                'string',
                'max:100',
                $this->editMode
                    ? Rule::unique('training_goals', 'code')->ignore($this->id)
                    : Rule::unique('training_goals', 'code'),
            ],
            'is_active' => ['boolean'],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $goal = $this->editMode
            ? TrainingGoal::findOrFail($this->id)
            : new TrainingGoal();

        $goal->fill($validated)->save();

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('site.goal_updated'));
            $this->mount($goal->fresh());
        } else {
            session()->flash('success', __('site.goal_created'));
        }



        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.training-goals.index'), navigate: true);
        }

        return $this->redirect(route('tenant.dashboard.training-goals.edit', $goal), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.training-goals.form');
    }
}
