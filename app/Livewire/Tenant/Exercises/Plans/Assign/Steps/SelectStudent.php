<?php

namespace App\Livewire\Tenant\Exercises\Plans\Assign\Steps;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Tenant\Student;

class SelectStudent extends Component
{
    use WithPagination;

    /** Estado compartido (recibido del wizard) */
    public array $state = [];

    /** Buscador */
    public string $q = '';

    /** Al seleccionar alumno(s) se guarda en el state del padre */
    public function toggleStudent(int $id): void
    {
        $ids = collect($this->state['student_ids'] ?? []);

        if ($ids->contains($id)) {
            $ids = $ids->reject(fn($x) => $x === $id);
        } else {
            $ids->push($id);
        }

        $this->state['student_ids'] = $ids->values()->all();
    }

    /** Continuar al paso siguiente */
    public function next(): void
    {
        if (empty($this->state['student_ids'])) {
            $this->dispatch('toast', type: 'warning', message: 'Seleccioná al menos un alumno.');
            return;
        }
        $this->dispatch('next-step', state: $this->state);
    }

    /** Escucha del padre para actualizar el state */
    #[On('update-state')]
    public function updateState(array $newState): void
    {
        $this->state = $newState;
    }

    public function getStudentsProperty()
    {
        return Student::query()
            ->when($this->q, fn($q) =>
                $q->where('first_name', 'like', "%{$this->q}%")
                  ->orWhere('last_name', 'like', "%{$this->q}%")
                  ->orWhere('email', 'like', "%{$this->q}%")
            )
            ->orderBy('first_name')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.tenant.exercises.plans.assign.steps.select-student', [
            'students' => $this->students,
        ]);
    }
}
