<?php

namespace App\Livewire\Tenant\Students;

use Livewire\Component;
use App\Models\Tenant\Student;

class StudentPlansList extends Component
{
    public ?Student $student = null;
    public array $assignments = [];

    public function mount(Student $student): void
    {
        $this->student = $student;
        $this->loadAssignments();
    }

    public function loadAssignments(): void
    {
        $this->assignments = $this->student->planAssignments()
            ->orderByDesc('is_active')
            ->orderByDesc('created_at')
            ->with('plan')
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.tenant.students.plans-list');
    }
}
