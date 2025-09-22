<?php

namespace App\Livewire\Tenant\Exercises\Equipments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\Equipment;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $id = null;
    public bool $editMode = false;

    public string $name = '';
    public string $code = '';
    public bool $is_machine = false;
    public ?string $description = null;
    public string $status = Equipment::STATUS_DRAFT;

    public bool $back = true;

    public function mount(?Equipment $equipment): void
    {
        if ($equipment && $equipment->exists) {
            $this->editMode    = true;
            $this->id          = (int) $equipment->id;
            $this->name        = (string) $equipment->name;
            $this->code        = (string) $equipment->code;
            $this->is_machine  = (bool) $equipment->is_machine;
            $this->description = $equipment->description;
            $this->status      = (string) ($equipment->status ?? Equipment::STATUS_DRAFT);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required','string','max:100',
                $this->editMode
                    ? Rule::unique('exercise_equipment', 'name')->ignore($this->id)
                    : Rule::unique('exercise_equipment', 'name'),
            ],
            'code' => [
                'required','string','max:100',
                $this->editMode
                    ? Rule::unique('exercise_equipment', 'code')->ignore($this->id)
                    : Rule::unique('exercise_equipment', 'code'),
            ],
            'is_machine'  => ['boolean'],
            'description' => ['nullable','string'],
            'status' => ['required', Rule::in([
                Equipment::STATUS_DRAFT,
                Equipment::STATUS_PUBLISHED,
                Equipment::STATUS_ARCHIVED,
            ])],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        /** @var Equipment $eq */
        $eq = $this->editMode
            ? Equipment::findOrFail($this->id)
            : new Equipment();

        $eq->fill([
            'name'        => $validated['name'],
            'code'        => $validated['code'],
            'is_machine'  => (bool) ($validated['is_machine'] ?? false),
            'description' => $validated['description'] ?? null,
            'status'      => $validated['status'],
        ])->save();

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('exercise.equipment_updated'));
            $this->mount($eq->fresh());
        } else {
            session()->flash('success', __('exercise.equipment_created'));
        }

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.exercise.equipments.index'), navigate: true);
        }

        return $this->redirect(route('tenant.dashboard.exercise.equipments.edit', $eq), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.exercises.equipments.form');
    }
}
