<?php

namespace App\Livewire\Tenant\Students;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Tenant\Student;
use App\Models\Tenant\CommercialPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $status = null;
    public ?int $plan = null;
    public string $sortBy = 'last_name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    public ?string $deleteUuid = null;
    public ?string $selectedStudentUuid = null;

    /** @var array<string> */
    protected array $sortableColumns = ['first_name', 'last_name', 'email', 'status', 'last_login_at'];

    public function updated($field): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (!in_array($column, $this->sortableColumns, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'plan']);
        $this->resetPage();
    }

    public function confirmDelete(string $uuid): void
    {
        $this->deleteUuid = $uuid;
    }

    public function delete(): void
    {
        if (! $this->deleteUuid) {
            return;
        }

        $student = Student::where('uuid', $this->deleteUuid)->first();

        if (! $student) {
            $this->deleteUuid = null;
            return;
        }

        DB::transaction(function () use ($student): void {
            $user = $student->user;

            if (! $user && $student->email) {
                $user = User::where('email', $student->email)->first();
            }

            if ($user) {
                // User deletion also removes linked student and assignments.
                $user->delete();
                return;
            }

            // Fallback for orphan students without linked user.
            $student->planAssignments()->delete();
            $student->delete();
        });

        $this->deleteUuid = null;
        $this->dispatch('student-deleted');
    }

    public function selectStudent(string $uuid): void
    {
        $this->selectedStudentUuid = $uuid;
    }

    #[On('plan-assigned')]
    public function refreshStudents(): void
    {
        $this->resetPage();
    }

    public function startConversation(int $studentId): void
    {
        $messagingService = app(\App\Services\Tenant\MessagingService::class);

        $conversation = $messagingService->findOrCreateConversation($studentId);

        $this->redirect(route('tenant.dashboard.messages.conversations.show', $conversation));
    }

    public function render()
    {
        $students = Student::query()
            ->with(['commercialPlan', 'currentPlanAssignment'])
            // Búsqueda agrupada para evitar problemas de precedencia con orWhere
            ->when($this->search, function ($q) {
                $t = "%{$this->search}%";
                $q->where(function ($qq) use ($t) {
                    $qq->where('first_name', 'like', $t)
                        ->orWhere('last_name', 'like', $t)
                        ->orWhere('email', 'like', $t);
                });
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->plan, fn ($q) => $q->where('commercial_plan_id', $this->plan))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.students.index', [
            'students' => $students,
            'plans' => CommercialPlan::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
