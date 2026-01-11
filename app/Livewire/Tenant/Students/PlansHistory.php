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

        if ($assignment->status->value === 'active') {
            session()->flash('error', __('students.cannot_delete_active_plan'));
            $this->dispatch('modal-close', name: 'confirm-delete-assignment');
            return;
        }

        $assignment->delete();
        $this->deleteAssignmentUuid = null;
        $this->deleteAssignmentName = null;
        session()->flash('success', __('students.plan_deleted'));
        $this->dispatch('modal-close', name: 'confirm-delete-assignment');
    }

    public function activateNow(string $assignmentUuid): void
    {
        $assignment = $this->student->planAssignments()
            ->where('uuid', $assignmentUuid)
            ->firstOrFail();

        if ($assignment->status->value !== 'pending') {
            session()->flash('error', 'Solo se pueden activar planes próximos.');
            return;
        }

        // Cancelar el plan activo actual si existe
        $currentActive = $this->student->planAssignments()
            ->where('status', 'active')
            ->first();

        if ($currentActive) {
            $currentActive->update([
                'status' => 'cancelled',
                'ends_at' => now(),
            ]);
        }

        // Activar el plan pendiente
        $assignment->update([
            'status' => 'active',
            'starts_at' => now(),
        ]);

        session()->flash('success', 'Plan activado correctamente.');
        $this->dispatch('modal-close', name: 'confirm-activate-assignment');
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
