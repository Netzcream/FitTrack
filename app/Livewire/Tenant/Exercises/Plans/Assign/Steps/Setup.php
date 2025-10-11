<?php

namespace App\Livewire\Tenant\Exercises\Plans\Assign\Steps;

use Livewire\Component;
use Carbon\Carbon;

class Setup extends Component
{
    public array $state = [];

    public function mount(array $state): void
    {
        $this->state = $state;

        // Si no hay fecha inicial, usamos hoy
        if (empty($this->state['start_date'])) {
            $this->state['start_date'] = Carbon::today()->format('Y-m-d');
        }
    }

    /** Guardar y avanzar */
    public function next(): void
    {
        if (empty($this->state['start_date'])) {
            $this->dispatch('toast', type: 'warning', message: 'Elegí una fecha de inicio.');
            return;
        }

        $this->dispatch('next-step', state: $this->state);
    }

    /** Volver al paso anterior */
    public function back(): void
    {
        $this->dispatch('prev-step', state: $this->state);
    }

    public function render()
    {
        return view('livewire.tenant.exercises.plans.assign.steps.setup');
    }
}
