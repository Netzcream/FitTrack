<?php

namespace App\Livewire\Tenant\Exercises\MovementPatterns;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\MovementPattern;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $id = null;
    public bool $editMode = false;

    public string $name = '';
    public string $code = '';
    public ?string $description = null;
    public string $status = MovementPattern::STATUS_DRAFT;

    public bool $back = true;

    public function mount(?MovementPattern $movementPattern): void
    {
        if ($movementPattern && $movementPattern->exists) {
            $this->editMode    = true;
            $this->id          = (int) $movementPattern->id;
            $this->name        = (string) $movementPattern->name;
            $this->code        = (string) $movementPattern->code;
            $this->description = $movementPattern->description;
            $this->status      = (string) ($movementPattern->status ?? MovementPattern::STATUS_DRAFT);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required','string','max:100',
                $this->editMode
                    ? Rule::unique('exercise_movement_patterns', 'name')->ignore($this->id)
                    : Rule::unique('exercise_movement_patterns', 'name'),
            ],
            'code' => [
                'required','string','max:100',
                $this->editMode
                    ? Rule::unique('exercise_movement_patterns', 'code')->ignore($this->id)
                    : Rule::unique('exercise_movement_patterns', 'code'),
            ],
            'description' => ['nullable','string'],
            'status' => ['required', Rule::in([
                MovementPattern::STATUS_DRAFT,
                MovementPattern::STATUS_PUBLISHED,
                MovementPattern::STATUS_ARCHIVED,
            ])],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        /** @var MovementPattern $pattern */
        $pattern = $this->editMode
            ? MovementPattern::findOrFail($this->id)
            : new MovementPattern();

        $pattern->fill([
            'name'        => $validated['name'],
            'code'        => $validated['code'],
            'description' => $validated['description'] ?? null,
            'status'      => $validated['status'],
        ])->save();

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('exercise.movement_pattern_updated'));
            $this->mount($pattern->fresh());
        } else {
            session()->flash('success', __('exercise.movement_pattern_created'));
        }

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.exercise.movement-patterns.index'), navigate: true);
        }

        return $this->redirect(route('tenant.dashboard.exercise.movement-patterns.edit', $pattern), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.exercises.movement-patterns.form');
    }
}
