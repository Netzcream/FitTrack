<?php

namespace App\Livewire\Tenant\CommercialPlans;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\CommercialPlan;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'sort_order';
    public string $sortDirection = 'asc';
    public string $search = '';

    public ?string $visibility = null;      // public|private
    public ?string $planType = null;        // free|standard|pro|enterprise
    public ?string $billingInterval = null; // monthly|yearly|both
    public $active = null;            // true|false

    public int $perPage = 10;
    public ?int $planToDelete = null;

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
        $this->planToDelete = $id;
    }

    public function delete(): void
    {
        if (!$this->planToDelete) return;
        if ($plan = CommercialPlan::find($this->planToDelete)) {
            $plan->delete(); // soft delete
        }
        $this->dispatch('plan-deleted');
        $this->reset('planToDelete');
    }

    public function render()
    {
        $plans = CommercialPlan::query()
            ->search($this->search)
            ->when($this->visibility, fn($q) => $q->where('visibility', $this->visibility))
            ->when($this->planType, fn($q) => $q->where('plan_type', $this->planType))
            ->when($this->billingInterval, fn($q) => $q->where('billing_interval', $this->billingInterval))
            ->when(!empty($this->active), fn($q) => $q->where('is_active', $this->active == 'yes' ? true : false))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.commercial-plans.index', compact('plans'));
    }
}
