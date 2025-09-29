<?php

namespace App\Livewire\Tenant\Exercises\Plans\Templates;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use App\Models\Tenant\Exercise\ExercisePlanTemplate;

#[Layout('components.layouts.tenant')]
class Edit extends Component
{
    public ?ExercisePlanTemplate $template = null;

    // Campos del header-form
    public string $name = '';
    public string $code = '';
    public string $status = 'draft';
    public bool $is_public = false;
    public ?string $description = null;

    public bool $editMode = false;
    public bool $justCreatedDraft = false;

    public function mount(?ExercisePlanTemplate $template)
    {
        // Si venís por /create sin modelo, creo borrador y redirijo a /edit
        if (!$template?->exists) {
            $tpl = ExercisePlanTemplate::create([
                'name'        => '',
                'code'        => strtoupper(uniqid('TPL')),
                'status'      => 'draft',
                'is_public'   => false,
                'description' => null,
                'version'     => 1,
            ]);
            $this->justCreatedDraft = true;

            // Importante: redirigimos a la URL con ID para que el builder funcione sin condicionales.
            return $this->redirectRoute(
                'tenant.dashboard.exercises.plans.templates.edit',
                ['template' => $tpl->id],
                navigate: true
            );
        }

        $this->template = $template;
        $this->editMode = true;

        // Seed de campos
        $this->name        = $template->name ?? '';
        $this->code        = $template->code ?? '';
        $this->status      = $template->status ?? 'draft';
        $this->is_public   = (bool) $template->is_public;
        $this->description = $template->description;
    }

    protected function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:150'],
            'code'       => [
                'required',
                'string',
                'max:80',
                Rule::unique('exercise_plan_templates', 'code')->ignore($this->template?->id)
            ],
            'status'     => ['required', Rule::in(['draft', 'published', 'archived'])],
            'is_public'  => ['boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function saveHeader(): void
    {
        $this->validate();

        $data = [
            'name'        => $this->name,
            'code'        => $this->code,
            'status'      => $this->status,
            'is_public'   => $this->is_public,
            'description' => $this->description,
        ];

        $this->template->update($data);

        // Aviso visual + opcionalmente refrescar título en el builder
        $this->dispatch('toast', type: 'success', message: 'Datos de la plantilla guardados.');
        // Si quisieras, podés notificar al builder que refresque su header:
        $this->dispatch('template-header-updated');
    }

    public function render()
    {
        return view('livewire.tenant.exercises.plans.templates.edit');
    }
}
