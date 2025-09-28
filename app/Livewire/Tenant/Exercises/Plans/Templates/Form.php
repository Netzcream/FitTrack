<?php

namespace App\Livewire\Tenant\Exercises\Plans\Templates;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\ExercisePlanTemplate;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?ExercisePlanTemplate $template = null;

    // Fields
    public string $name = '';
    public string $code = '';
    public string $status = 'draft';
    public bool $is_public = false;
    public ?string $description = null;

    public bool $editMode = false;

    public function mount(?ExercisePlanTemplate $template): void
    {
        $this->template = $template?->exists ? $template : null;
        $this->editMode = (bool) $this->template;

        if ($this->editMode) {
            $this->name = $this->template->name;
            $this->code = $this->template->code;
            $this->status = $this->template->status;
            $this->is_public = (bool)$this->template->is_public;
            $this->description = $this->template->description;
        }
    }

    protected function rules(): array
    {
        return [
            'name'       => ['required','string','max:150'],
            'code'       => [
                'required','string','max:80',
                Rule::unique('exercise_plan_templates','code')->ignore($this->template?->id)
            ],
            'status'     => ['required', Rule::in(['draft','published','archived'])],
            'is_public'  => ['boolean'],
            'description'=> ['nullable','string'],
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'code'        => $this->code,
            'status'      => $this->status,
            'is_public'   => $this->is_public,
            'description' => $this->description,
        ];

        if ($this->editMode) {
            $this->template->update($data);
            $id = $this->template->id;
            $this->dispatch('toast', type: 'success', message: 'Plantilla actualizada.');
        } else {
            $tpl = ExercisePlanTemplate::create($data + ['version' => 1]);
            $id = $tpl->id;
            $this->dispatch('toast', type: 'success', message: 'Plantilla creada.');
        }

        // Redirige al builder en el próximo paso (opcional), por ahora volvemos al index.
        return $this->redirectRoute('tenant.dashboard.exercises.plans.templates.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.exercises.plans.templates.form');
    }
}
