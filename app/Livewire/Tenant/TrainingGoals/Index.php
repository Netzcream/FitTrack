<?php

namespace App\Livewire\Tenant\TrainingGoals;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\TrainingGoal;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public string $search = '';
    public $active = null; // true|false
    public int $perPage = 10;

    public ?int $goalToDelete = null;

    public function sort(string $column): void
    {
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
        $this->goalToDelete = $id;
    }

    public function delete(): void
    {
        if (!$this->goalToDelete) return;
        if ($goal = TrainingGoal::find($this->goalToDelete)) {
            $goal->delete(); // soft delete
        }
        $this->dispatch('goal-deleted');
        $this->reset('goalToDelete');
    }

    public function render()
    {
        $goals = TrainingGoal::query()
            ->when(
                $this->search !== '',
                fn($q) =>
                $q->where(
                    fn($qq) =>
                    $qq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                )
            )
            ->when(!empty($this->active), fn($q) => $q->where('is_active', $this->active=='yes'?true:false))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.training-goals.index', compact('goals'));
    }
}
