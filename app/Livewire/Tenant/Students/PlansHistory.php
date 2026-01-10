<?php

namespace App\Livewire\Tenant\Students;

use App\Models\Tenant\Student;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('components.layouts.tenant')]
class PlansHistory extends Component
{
    use WithPagination;

    public Student $student;
    public ?string $deleteAssignmentUuid = null;
    public ?string $deleteAssignmentName = null;

    #[Url]
    public ?string $back = 'index';

    protected string $paginationTheme = 'tailwind';

    public function mount(Student $student): void
    {
        $this->student = $student;
    }

    public function confirmDelete(string $assignmentUuid): void
    {
        $assignment = $this->student->planAssignments()
            ->where('uuid', $assignmentUuid)
            ->firstOrFail();

        $this->deleteAssignmentUuid = $assignment->uuid;
        $this->deleteAssignmentName = $assignment->name;
    }

    public function deleteAssignment(): void
    {
        if (!$this->deleteAssignmentUuid) {
            return;
        }

        $assignment = $this->student->planAssignments()
            ->where('uuid', $this->deleteAssignmentUuid)
            ->firstOrFail();

        if ($assignment->is_active) {
            session()->flash('error', __('students.cannot_delete_active_plan'));
            return;
        }

        $assignment->delete();
        $this->deleteAssignmentUuid = null;
        $this->deleteAssignmentName = null;
        session()->flash('success', __('students.plan_deleted'));
    }

    public function render()
    {
        $assignments = $this->student
            ->planAssignments()
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->paginate(5);

        return view('livewire.tenant.students.plans-history', [
            'assignments' => $assignments,
        ]);
    }
}
