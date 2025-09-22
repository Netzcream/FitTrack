<?php

namespace App\Livewire\Tenant\Exercises\Muscles;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\Muscle;
use App\Models\Tenant\Exercise\MuscleGroup;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $status = '';
    public ?int $muscle_group_id = null;
    public int $perPage = 10;

    public ?int $muscleToDelete = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function filter(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->muscleToDelete = $id;
    }

    public function delete(): void
    {
        if (!$this->muscleToDelete) return;

        if ($muscle = Muscle::find($this->muscleToDelete)) {
            $muscle->delete();
        }

        $this->dispatch('muscle-deleted');
        $this->reset('muscleToDelete');
    }

    public function moveUp(int $id): void { $this->move($id, 'up'); }
    public function moveDown(int $id): void { $this->move($id, 'down'); }

    protected function move(int $id, string $direction): void
    {
        /** @var Muscle $current */
        $current = Muscle::findOrFail($id);

        if ($current->order === null) {
            $max = (int) Muscle::max('order');
            $current->order = $max + 1;
            $current->save();
        }

        $minOrder = (int) (Muscle::min('order') ?? 0);
        $maxOrder = (int) (Muscle::max('order') ?? 0);

        if ($direction === 'up') {
            if ($current->order <= $minOrder) return;
            $neighbor = Muscle::where('order', '<', $current->order)
                ->orderBy('order', 'desc')->first();
        } else {
            if ($current->order >= $maxOrder) return;
            $neighbor = Muscle::where('order', '>', $current->order)
                ->orderBy('order', 'asc')->first();
        }

        if (!$neighbor) return;

        DB::transaction(function () use ($current, $neighbor) {
            $tmp = $current->order;
            $current->order = $neighbor->order;
            $neighbor->order = $tmp;
            $current->save();
            $neighbor->save();
        });

        $this->resetPage();
    }

    public function render()
    {
        $base = Muscle::query()
            ->when($this->search !== '', fn($q) =>
                $q->where(fn($qq) =>
                    $qq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('code', 'like', "%{$this->search}%")
                       ->orWhereHas('group', fn($g) =>
                           $g->where('name', 'like', "%{$this->search}%")
                       )
                )
            )
            ->when($this->status !== null && $this->status !== '', fn($q) =>
                $q->where('status', $this->status)
            )
            ->when($this->muscle_group_id, fn($q) =>
                $q->where('muscle_group_id', $this->muscle_group_id)
            )
            ->with('group:id,name');

        // min/max sobre el conjunto filtrado
        $minOrder = (int) (((clone $base)->min('order')) ?? 0);
        $maxOrder = (int) (((clone $base)->max('order')) ?? 0);

        $muscles = (clone $base)
            ->orderByRaw('CASE WHEN `order` IS NULL THEN 1 ELSE 0 END, `order` ASC')
            ->orderBy('name')
            ->paginate($this->perPage);

        $groupOptions = MuscleGroup::orderBy('name')->get(['id', 'name']);

        return view('livewire.tenant.exercises.muscles.index', [
            'muscles'      => $muscles,
            'minOrder'     => $minOrder,
            'maxOrder'     => $maxOrder,
            'groupOptions' => $groupOptions,
        ]);
    }
}
