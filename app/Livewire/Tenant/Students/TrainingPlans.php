<?php

namespace App\Livewire\Tenant\Students;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Student;
use App\Models\Tenant\TrainingPlan;

#[Layout('components.layouts.tenant')]
class TrainingPlans extends Component
{
    public Student $student;

    public function mount(Student $student): void
    {
        $this->student = $student;
    }
    public function duplicatePlan(string $uuid): void
    {
        $original = TrainingPlan::with(['exercises', 'media'])->where('uuid', $uuid)->firstOrFail();
        $clone = $original->duplicate();
        $clone->student_id = $this->student->id;
        $clone->is_active = true;
        $clone->save();

        $this->dispatch('plan-cloned');
    }


    public function render()
    {
        $plans = TrainingPlan::where('student_id', $this->student->id)
            ->orderByDesc('assigned_from')
            ->get();

        return view('livewire.tenant.students.training-plans', [
            'student' => $this->student,
            'plans'   => $plans,
        ]);
    }
}
