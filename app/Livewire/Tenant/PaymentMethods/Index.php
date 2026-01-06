<?php

namespace App\Livewire\Tenant\PaymentMethods;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\PaymentMethod;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public string $search = '';
    public string $status = ''; // "1" | "0" | ""
    public int $perPage = 10;

    public ?int $methodToDelete = null;

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updating($field): void
    {
        if (in_array($field, ['search', 'status'])) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->methodToDelete = $id;
    }

    public function delete(): void
    {
        if (!$this->methodToDelete) return;

        if ($method = PaymentMethod::find($this->methodToDelete)) {
            $method->delete(); // soft delete
        }

        $this->dispatch('payment-method-deleted');
        $this->reset('methodToDelete');
    }

    public function render()
    {
        $methods = PaymentMethod::query()
            ->when($this->search !== '', fn($q) =>
                $q->where(fn($qq) =>
                    $qq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('code', 'like', "%{$this->search}%")
                )
            )
            ->when($this->status !== '', fn($q) =>
                $q->where('is_active', $this->status === '1')
            )
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.payment-methods.index', compact('methods'));
    }
}
