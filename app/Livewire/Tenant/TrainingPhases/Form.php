<?php

namespace App\Livewire\Tenant\TrainingPhases;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\TrainingPhase;
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

    public function mount(?TrainingPhase $trainingPhase): void
    {
        if ($trainingPhase && $trainingPhase->exists) {
            $this->editMode  = true;
            $this->id        = (int) $trainingPhase->id;
            $this->name      = (string) $trainingPhase->name;
            $this->code      = (string) $trainingPhase->code;
            $this->is_active = (bool) $trainingPhase->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required', 'string', 'max:100',
                $this->editMode
                    ? Rule::unique('training_phases', 'code')->ignore($this->id)
                    : Rule::unique('training_phases', 'code'),
            ],
            'is_active' => ['boolean'],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $phase = $this->editMode
            ? TrainingPhase::findOrFail($this->id)
            : new TrainingPhase();

        $phase->fill($validated)->save();

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('site.phase_updated'));
            $this->mount($phase->fresh());
        } else {
            session()->flash('success', __('site.phase_created'));
        }

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.training-phases.index'), navigate: true);
        }

        return $this->redirect(route('tenant.dashboard.training-phases.edit', $phase), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.training-phases.form');
    }
}
