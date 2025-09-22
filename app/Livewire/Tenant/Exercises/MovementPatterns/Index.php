<?php

namespace App\Livewire\Tenant\Exercises\MovementPatterns;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\MovementPattern;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $status = '';
    public int $perPage = 10;

    public ?int $movementPatternToDelete = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function filter(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->movementPatternToDelete = $id;
    }

    public function delete(): void
    {
        if (!$this->movementPatternToDelete) return;

        if ($pattern = MovementPattern::find($this->movementPatternToDelete)) {
            $pattern->delete();
        }

        $this->dispatch('movement-pattern-deleted');
        $this->reset('movementPatternToDelete');
    }

    public function moveUp(int $id): void { $this->move($id, 'up'); }
    public function moveDown(int $id): void { $this->move($id, 'down'); }

    protected function move(int $id, string $direction): void
    {
        /** @var MovementPattern $current */
        $current = MovementPattern::findOrFail($id);

        if ($current->order === null) {
            $max = (int) MovementPattern::max('order');
            $current->order = $max + 1;
            $current->save();
        }

        $minOrder = (int) (MovementPattern::min('order') ?? 0);
        $maxOrder = (int) (MovementPattern::max('order') ?? 0);

        if ($direction === 'up') {
            if ($current->order <= $minOrder) return;
            $neighbor = MovementPattern::where('order', '<', $current->order)
                ->orderBy('order', 'desc')->first();
        } else {
            if ($current->order >= $maxOrder) return;
            $neighbor = MovementPattern::where('order', '>', $current->order)
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
        $base = MovementPattern::query()
            ->when($this->search !== '', fn($q) =>
                $q->where(fn($qq) =>
                    $qq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('code', 'like', "%{$this->search}%")
                )
            )
            ->when($this->status !== null && $this->status !== '', fn($q) =>
                $q->where('status', $this->status)
            );

        $minOrder = (int) (((clone $base)->min('order')) ?? 0);
        $maxOrder = (int) (((clone $base)->max('order')) ?? 0);

        $movementPatterns = (clone $base)
            ->orderByRaw('CASE WHEN `order` IS NULL THEN 1 ELSE 0 END, `order` ASC')
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.tenant.exercises.movement-patterns.index', [
            'movementPatterns' => $movementPatterns,
            'minOrder' => $minOrder,
            'maxOrder' => $maxOrder,
        ]);
    }
}
