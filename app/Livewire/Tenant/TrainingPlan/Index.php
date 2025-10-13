<?php

namespace App\Livewire\Tenant\TrainingPlan;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\TrainingPlan;
use Illuminate\Database\Eloquent\Builder;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public string $status = '';
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public ?string $deleteUuid = null;

    protected $paginationTheme = 'tailwind';

    /* -------------------- Reactividad -------------------- */
    public function updating($field)
    {
        if (in_array($field, ['q', 'status'])) {
            $this->resetPage();
        }
    }

    /* -------------------- Ordenamiento -------------------- */
    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    /* -------------------- Filtros -------------------- */
    public function resetFilters(): void
    {
        $this->q = '';
        $this->status = '';
        $this->resetPage();
    }

    /* -------------------- Eliminación -------------------- */
    public function confirmDelete(string $uuid): void
    {
        $this->deleteUuid = $uuid;
    }

    public function delete(): void
    {
        if ($this->deleteUuid) {
            TrainingPlan::where('uuid', $this->deleteUuid)->delete();
            $this->dispatch('plan-deleted');
            $this->deleteUuid = null;
        }
    }

    /* -------------------- Render -------------------- */
    public function render()
    {
        $plans = TrainingPlan::query()
            ->when($this->status !== '', fn (Builder $q) => $q->where('is_active', (bool) $this->status))
            ->search($this->q)
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.tenant.training-plan.index', compact('plans'));
    }
}
