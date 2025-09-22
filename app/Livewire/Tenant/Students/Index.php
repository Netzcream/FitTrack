<?php

namespace App\Livewire\Tenant\Students;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Student;
use App\Models\Tenant\CommercialPlan;
use App\Models\Tenant\TrainingGoal;
use App\Models\Tenant\Tag;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $first_name = '';
    public string $last_name  = '';
    public ?string $phone     = null;
    public ?string $email     = null;

    public string $sortBy = 'last_name';
    public string $sortDirection = 'asc';
    public string $search = '';

    public ?string $status = null; // active|paused|inactive|prospect
    public ?int $planId = null;
    public ?int $goalId = null;
    public ?int $tagId = null;

    public int $perPage = 10;

    public ?int $studentToDelete = null;



    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'email'      => ['nullable', 'email', 'max:120'],
        ];
    }


    public function saveStudent()
    {
        $data = $this->validate();

        $student = Student::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'phone'      => $data['phone'] ?? null,
            'email'      => $data['email'] ?? null,
        ]);

        // Redirige directo a la edición del alumno
        return redirect()->route('tenant.dashboard.students.edit', $student);
    }



    /* ---------- UX helpers (mismo patrón) ---------- */
    public function sort(string $column): void
    {
        // Permitimos sólo columnas reales de students
        $allowed = ['last_name', 'status', 'last_login_at', 'avg_adherence_pct', 'first_name', 'email'];
        if (! in_array($column, $allowed, true)) {
            $column = 'last_name';
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function filter(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->studentToDelete = $id;
    }

    public function delete(): void
    {
        if (!$this->studentToDelete) return;

        if ($s = Student::find($this->studentToDelete)) {
            $s->delete(); // soft delete
        }

        $this->dispatch('student-deleted');
        $this->reset('studentToDelete');
    }

    public function render()
    {
        $students = Student::query()
            ->with([
                'commercialPlan:id,name',
                'primaryTrainingGoal:id,name',
            ])
            ->when($this->search !== '', function ($q) {
                $s = "%{$this->search}%";
                $q->where(
                    fn($qq) =>
                    $qq->where('first_name', 'like', $s)
                        ->orWhere('last_name', 'like', $s)
                        ->orWhere('email', 'like', $s)
                        ->orWhere('phone', 'like', $s)
                        ->orWhere('document_number', 'like', $s)
                );
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->planId, fn($q) => $q->where('commercial_plan_id', $this->planId))
            ->when($this->goalId, fn($q) => $q->where('primary_training_goal_id', $this->goalId))
            ->when(
                $this->tagId,
                fn($q) =>
                $q->whereHas('tags', fn($t) => $t->where('tags.id', $this->tagId))
            )
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        $plans = CommercialPlan::query()->orderBy('name')->get(['id', 'name']);
        $goals = TrainingGoal::query()->orderBy('name')->get(['id', 'name']);
        $tags  = Tag::query()->orderBy('name')->get(['id', 'name', 'color']);

        return view('livewire.tenant.students.index', compact('students', 'plans', 'goals', 'tags'));
    }
}
