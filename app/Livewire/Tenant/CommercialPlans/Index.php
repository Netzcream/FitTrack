<?php

namespace App\Livewire\Tenant\CommercialPlans;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Tenant\CommercialPlan;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $status = '';
    public int $perPage = 10;

    public ?int $deleteId = null;

    public function updating($field)
    {
        if (in_array($field, ['search', 'status'])) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status']);
    }

    public function moveUp(int $id): void
    {
        $item = CommercialPlan::findOrFail($id);
        $above = CommercialPlan::where('order', '<', $item->order)
            ->orderByDesc('order')
            ->first();

        if ($above) {
            $temp = $item->order;
            $item->order = $above->order;
            $above->order = $temp;
            $item->save();
            $above->save();
        }
    }

    public function moveDown(int $id): void
    {
        $item = CommercialPlan::findOrFail($id);
        $below = CommercialPlan::where('order', '>', $item->order)
            ->orderBy('order')
            ->first();

        if ($below) {
            $temp = $item->order;
            $item->order = $below->order;
            $below->order = $temp;
            $item->save();
            $below->save();
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            CommercialPlan::findOrFail($this->deleteId)->delete();
            $this->dispatch('plan-deleted');
            $this->deleteId = null;
        }
    }

    public function clone($uuid) {

    }


    public function render()
    {
        $plans = CommercialPlan::query()
            ->search($this->search)
            ->when($this->status !== '', function (Builder $q) {
                $q->where('is_active', $this->status === '1');
            })
            ->ordered()
            ->paginate($this->perPage);

        return view('livewire.tenant.commercial-plans.index', [
            'plans' => $plans,
        ]);
    }
}
