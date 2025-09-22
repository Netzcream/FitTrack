<?php

namespace App\Livewire\Tenant\Exercises\ExercisePlanes;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\ExercisePlane;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $status = '';
    public int $perPage = 10;

    public ?int $exercisePlaneToDelete = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function filter(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->exercisePlaneToDelete = $id;
    }

    public function delete(): void
    {
        if (!$this->exercisePlaneToDelete) return;

        if ($plane = ExercisePlane::find($this->exercisePlaneToDelete)) {
            $plane->delete();
        }

        $this->dispatch('exercise-plane-deleted');
        $this->reset('exercisePlaneToDelete');
    }

    public function moveUp(int $id): void { $this->move($id, 'up'); }
    public function moveDown(int $id): void { $this->move($id, 'down'); }

    protected function move(int $id, string $direction): void
    {
        /** @var ExercisePlane $current */
        $current = ExercisePlane::findOrFail($id);

        if ($current->order === null) {
            $max = (int) ExercisePlane::max('order');
            $current->order = $max + 1;
            $current->save();
        }

        $minOrder = (int) (ExercisePlane::min('order') ?? 0);
        $maxOrder = (int) (ExercisePlane::max('order') ?? 0);

        if ($direction === 'up') {
            if ($current->order <= $minOrder) return;
            $neighbor = ExercisePlane::where('order', '<', $current->order)
                ->orderBy('order', 'desc')->first();
        } else {
            if ($current->order >= $maxOrder) return;
            $neighbor = ExercisePlane::where('order', '>', $current->order)
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
        $base = ExercisePlane::query()
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

        $exercisePlanes = (clone $base)
            ->orderByRaw('CASE WHEN `order` IS NULL THEN 1 ELSE 0 END, `order` ASC')
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.tenant.exercises.exercise-planes.index', [
            'exercisePlanes' => $exercisePlanes,
            'minOrder' => $minOrder,
            'maxOrder' => $maxOrder,
        ]);
    }
}
