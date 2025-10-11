<?php

namespace App\Livewire\Tenant\Exercises\Plans\Assignments;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\ExercisePlanAssignment;
use App\Models\Tenant\Student;
use Illuminate\Database\Eloquent\Builder;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $q = ''; // búsqueda por alumno o plan
    public string $status = ''; // activo, finalizado, pendiente
    public ?string $date_from = null;
    public ?string $date_to = null;
    public int $perPage = 10;

    protected $queryString = [
        'q'         => ['except' => ''],
        'status'    => ['except' => ''],
        'date_from' => ['except' => null],
        'date_to'   => ['except' => null],
        'page'      => ['except' => 1],
    ];

    public function updatingQ()
    {
        $this->resetPage();
    }
    public function updatingStatus()
    {
        $this->resetPage();
    }

    protected function query(): Builder
    {
        return ExercisePlanAssignment::query()
            ->with(['student', 'plan'])
            ->when($this->q, function (Builder $q) {
                $q->whereHas(
                    'student',
                    fn($s) =>
                    $s->where('first_name', 'like', "%{$this->q}%")
                        ->orWhere('last_name', 'like', "%{$this->q}%")
                        ->orWhere('email', 'like', "%{$this->q}%")
                )->orWhereHas(
                    'plan',
                    fn($p) =>
                    $p->where('name', 'like', "%{$this->q}%")
                );
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->date_from, fn($q) => $q->whereDate('start_date', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('start_date', '<=', $this->date_to))
            ->orderByDesc('start_date');
    }

    public function finish(int $id): void
    {
        $assignment = ExercisePlanAssignment::findOrFail($id);
        $assignment->status = 'finished';
        $assignment->is_active = false;
        $assignment->end_date = now();
        $assignment->save();

        $this->dispatch('toast', type: 'success', message: 'Asignación finalizada correctamente.');
    }

    public function render()
    {
        $assignments = $this->query()->paginate($this->perPage);
        $students = Student::orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('livewire.tenant.exercises.plans.assignments.index', [
            'assignments' => $assignments,
            'students' => $students,
        ]);
    }
}
