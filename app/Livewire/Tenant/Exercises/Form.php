<?php

namespace App\Livewire\Tenant\Exercises;

use Livewire\Component;
use App\Models\Tenant\Exercise;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?Exercise $exercise = null;

    public string $name = '';
    public string $description = '';
    public string $category = '';
    public string $level = '';
    public string $equipment = '';
    public bool $is_active = true;

    public bool $editMode = false;
    public bool $back = false;

    public function mount(?Exercise $exercise): void
    {
        if ($exercise && $exercise->exists) {
            $this->exercise = $exercise;
            $this->name = $exercise->name;
            $this->description = $exercise->description ?? '';
            $this->category = $exercise->category ?? '';
            $this->level = $exercise->level ?? '';
            $this->equipment = $exercise->equipment ?? '';
            $this->is_active = $exercise->is_active;
            $this->editMode = true;
        }
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category'    => ['nullable', 'string', 'max:255'],
            'level'       => ['nullable', 'string', 'max:255'],
            'equipment'   => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

        $exercise = $this->editMode
            ? $this->exercise
            : new Exercise();

        $exercise->fill($data);

        if (!$this->editMode) {
            $exercise->uuid = (string) Str::uuid();
        }

        $exercise->save();

        $this->dispatch('saved');

        // 🔹 Si seleccionó "volver al listado"
        if ($this->back) {
            return redirect()->route('tenant.dashboard.exercises.index');
        }

        // 🔹 Si fue una creación nueva → redirigir a su formulario en modo edición
        if (!$this->editMode) {
            return redirect()->route('tenant.dashboard.exercises.edit', $exercise->uuid);
        }

        // 🔹 Caso edición normal → permanecer en la misma vista
        session()->flash(
            'success',
            $this->editMode
                ? __('exercises.updated')
                : __('exercises.created')
        );
    }


    public function render()
    {
        return view('livewire.tenant.exercises.form');
    }
}
