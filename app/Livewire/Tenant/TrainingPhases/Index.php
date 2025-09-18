<?php

namespace App\Livewire\Tenant\TrainingPhases;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\TrainingPhase;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public string $search = '';
    public $active = null; // "yes" | "no" | null
    public int $perPage = 10;

    public ?int $phaseToDelete = null;

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
        $this->phaseToDelete = $id;
    }

    public function delete(): void
    {
        if (!$this->phaseToDelete) return;

        if ($phase = TrainingPhase::find($this->phaseToDelete)) {
            $phase->delete(); // soft delete
        }

        $this->dispatch('phase-deleted');
        $this->reset('phaseToDelete');
    }

    public function render()
    {
        $phases = TrainingPhase::query()
            ->when($this->search !== '', function ($q) {
                $q->where(fn($qq) =>
                    $qq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('code', 'like', "%{$this->search}%")
                );
            })
            ->when(!empty($this->active), function ($q) {
                $q->where('is_active', $this->active === 'yes');
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.training-phases.index', compact('phases'));
    }
}
