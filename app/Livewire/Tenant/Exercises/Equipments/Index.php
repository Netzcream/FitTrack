<?php

namespace App\Livewire\Tenant\Exercises\Equipments;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\Equipment;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $status = '';
    public ?string $is_machine = ''; // '' | '1' | '0'
    public int $perPage = 10;

    public ?int $equipmentToDelete = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function filter(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->equipmentToDelete = $id;
    }

    public function delete(): void
    {
        if (!$this->equipmentToDelete) return;

        if ($eq = Equipment::find($this->equipmentToDelete)) {
            $eq->delete();
        }

        $this->dispatch('equipment-deleted');
        $this->reset('equipmentToDelete');
    }

    public function moveUp(int $id): void { $this->move($id, 'up'); }
    public function moveDown(int $id): void { $this->move($id, 'down'); }

    protected function move(int $id, string $direction): void
    {
        /** @var Equipment $current */
        $current = Equipment::findOrFail($id);

        if ($current->order === null) {
            $max = (int) Equipment::max('order');
            $current->order = $max + 1;
            $current->save();
        }

        $minOrder = (int) (Equipment::min('order') ?? 0);
        $maxOrder = (int) (Equipment::max('order') ?? 0);

        if ($direction === 'up') {
            if ($current->order <= $minOrder) return;
            $neighbor = Equipment::where('order', '<', $current->order)
                ->orderBy('order', 'desc')->first();
        } else {
            if ($current->order >= $maxOrder) return;
            $neighbor = Equipment::where('order', '>', $current->order)
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
        $base = Equipment::query()
            ->when($this->search !== '', fn($q) =>
                $q->where(fn($qq) =>
                    $qq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('code', 'like', "%{$this->search}%")
                )
            )
            ->when($this->status !== null && $this->status !== '', fn($q) =>
                $q->where('status', $this->status)
            )
            ->when($this->is_machine === '1' || $this->is_machine === '0', fn($q) =>
                $q->where('is_machine', $this->is_machine === '1')
            );

        // límites sobre el set filtrado
        $minOrder = (int) (((clone $base)->min('order')) ?? 0);
        $maxOrder = (int) (((clone $base)->max('order')) ?? 0);

        $equipment = (clone $base)
            ->orderByRaw('CASE WHEN `order` IS NULL THEN 1 ELSE 0 END, `order` ASC')
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.tenant.exercises.equipments.index', [
            'equipment' => $equipment,
            'minOrder'  => $minOrder,
            'maxOrder'  => $maxOrder,
        ]);
    }
}
