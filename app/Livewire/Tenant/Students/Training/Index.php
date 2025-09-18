<?php

namespace App\Livewire\Tenant\Students\Training;

use Livewire\Component;
use App\Models\Tenant\Student;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.tenant.students.settings')]
class Index extends Component
{
    public Student $student;

    public function mount(Student $student)
    {
        $this->student = $student;
    }

    public function render()
    {
        $aptInDays        = optional($this->student->apt_fitness_expires_at)?->diffInDays(now(), false);
        $aptExpiresInDays = $aptInDays !== null && $aptInDays >= 0 ? $aptInDays : null;

        /** @var \Illuminate\View\View $view */
        $view = view('livewire.tenant.students.training.index', [
            'student' => $this->student,
        ]);

        // 👇 ESTO es lo que alimenta al LAYOUT
        return $view->layoutData([
            'student'           => $this->student,
            'active'            => 'training',
            'overdueInvoices'   => 0,
            'aptExpiresInDays'  => $aptExpiresInDays,
            'unreadMessages'    => 0,
        ]);
    }
}
