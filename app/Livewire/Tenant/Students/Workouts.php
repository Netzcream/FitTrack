<?php

namespace App\Livewire\Tenant\Students;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Student;
use App\Models\Tenant\Workout;

class Workouts extends Component
{
    use WithPagination;

    public Student $student;
    public string $filterDateFrom = '';
    public string $filterDateTo = '';

    public function mount(Student $student)
    {
        $this->student = $student;
        $this->filterDateFrom = now()->subMonth()->format('Y-m-d');
        $this->filterDateTo = now()->format('Y-m-d');
    }

    public function updated($field): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Workout::query()
            ->where('student_id', $this->student->id)
            ->with('planAssignment')
            ->orderByDesc('completed_at');

        if ($this->filterDateFrom) {
            $query->whereDate('completed_at', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->whereDate('completed_at', '<=', $this->filterDateTo);
        }

        return view('livewire.tenant.students.workouts', [
            'workouts' => $query->paginate(10),
            'stats' => [
                'total' => Workout::where('student_id', $this->student->id)->where('status', 'completed')->count(),
                'thisMonth' => Workout::where('student_id', $this->student->id)
                    ->where('status', 'completed')
                    ->whereMonth('completed_at', now()->month)
                    ->whereYear('completed_at', now()->year)
                    ->count(),
                'avgRating' => round(
                    Workout::where('student_id', $this->student->id)
                        ->whereNotNull('rating')
                        ->avg('rating'),
                    1
                ),
            ],
        ]);
    }
}
