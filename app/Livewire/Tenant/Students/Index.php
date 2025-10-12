<?php

namespace App\Livewire\Tenant\Students;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Student;
use App\Models\Tenant\CommercialPlan;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public ?string $status = null;
    public ?int $plan = null;
    public string $sortBy = 'last_name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    public ?string $deleteUuid = null;

    public function updated($field): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['q', 'status', 'plan']);
    }

    public function confirmDelete(string $uuid): void
    {
        $this->deleteUuid = $uuid;
    }

    public function delete(): void
    {
        if ($this->deleteUuid) {
            Student::where('uuid', $this->deleteUuid)->delete();
            $this->deleteUuid = null;
            $this->dispatch('student-deleted');
        }
    }

    public function render()
    {
        $students = Student::query()
            ->with('commercialPlan')
            ->when($this->q, fn($q) => $q
                ->where('first_name', 'like', "%{$this->q}%")
                ->orWhere('last_name', 'like', "%{$this->q}%")
                ->orWhere('email', 'like', "%{$this->q}%"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->plan, fn($q) => $q->where('commercial_plan_id', $this->plan))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.students.index', [
            'students' => $students,
            'plans' => CommercialPlan::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
