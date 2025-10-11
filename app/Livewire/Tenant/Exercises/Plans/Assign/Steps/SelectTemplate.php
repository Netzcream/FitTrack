<?php

namespace App\Livewire\Tenant\Exercises\Plans\Assign\Steps;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Exercise\ExercisePlanTemplate;

class SelectTemplate extends Component
{
    use WithPagination;

    public array $state = [];
    public string $q = '';
    public string $objective = '';
    public string $level = '';

    /** Seleccionar plantilla */
    public function selectTemplate(int $id): void
    {
        $this->state['template_id'] = $id;
        $this->dispatch('next-step', state: $this->state);
    }


    public function getTemplatesProperty()
    {
        return ExercisePlanTemplate::query()
            ->with('workouts.blocks.items')
            ->where('status', 'published')
            ->when(
                $this->q,
                fn($q) =>
                $q->where('name', 'like', "%{$this->q}%")
                    ->orWhere('code', 'like', "%{$this->q}%")
            )
            ->orderBy('updated_at', 'desc')
            ->paginate(9);
    }

    public function render()
    {
        return view('livewire.tenant.exercises.plans.assign.steps.select-template', [
            'templates' => $this->templates,
        ]);
    }
}
