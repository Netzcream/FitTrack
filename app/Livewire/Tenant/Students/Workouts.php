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

    public function quickClone($workoutId)
    {
        try {
            $workout = Workout::findOrFail($workoutId);

            $cloned = $workout->clone([
                'date' => today(),
                'status' => 'pending',
                'rating' => null,
                'notes' => 'Clonado de ' . $workout->date->format('d/m/Y'),
            ]);

            session()->flash('success', 'Workout clonado para hoy');

        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }

    }

    public function render()
    {
        $query = Workout::query()
            ->where('student_id', $this->student->id)
            ->with('trainingPlan')
            ->orderByDesc('date');

        if ($this->filterDateFrom) {
            $query->whereDate('date', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->whereDate('date', '<=', $this->filterDateTo);
        }

        return view('livewire.tenant.students.workouts', [
            'workouts' => $query->paginate(10),
            'stats' => [
                'total' => Workout::where('student_id', $this->student->id)->count(),
                'thisMonth' => Workout::where('student_id', $this->student->id)
                    ->whereMonth('date', now()->month)
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
