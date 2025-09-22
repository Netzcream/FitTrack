<?php

namespace App\Livewire\Tenant\Exercises\ExerciseLevels;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\ExerciseLevel;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $status = '';
    public int $perPage = 10;

    public ?int $exerciseLevelToDelete = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function filter(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->exerciseLevelToDelete = $id;
    }

    public function delete(): void
    {
        if (!$this->exerciseLevelToDelete) return;
        if ($level = ExerciseLevel::find($this->exerciseLevelToDelete)) {
            $level->delete();
        }
        $this->dispatch('exercise-level-deleted');
        $this->reset('exerciseLevelToDelete');
    }

    public function moveUp(int $id): void { $this->move($id, 'up'); }
    public function moveDown(int $id): void { $this->move($id, 'down'); }

    protected function move(int $id, string $direction): void
    {
        /** @var ExerciseLevel $current */
        $current = ExerciseLevel::findOrFail($id);

        if ($current->order === null) {
            $max = (int) ExerciseLevel::max('order');
            $current->order = $max + 1;
            $current->save();
        }

        $minOrder = (int) (ExerciseLevel::min('order') ?? 0);
        $maxOrder = (int) (ExerciseLevel::max('order') ?? 0);

        if ($direction === 'up') {
            if ($current->order <= $minOrder) return;
            $neighbor = ExerciseLevel::where('order', '<', $current->order)
                ->orderBy('order', 'desc')->first();
        } else {
            if ($current->order >= $maxOrder) return;
            $neighbor = ExerciseLevel::where('order', '>', $current->order)
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
        $base = ExerciseLevel::query()
            ->when($this->search !== '', fn($q) =>
                $q->where(fn($qq) =>
                    $qq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('code', 'like', "%{$this->search}%")
                )
            )
            ->when($this->status !== null && $this->status !== '', fn($q) =>
                $q->where('status', $this->status)
            );

        // min/max sobre el conjunto filtrado
        $minOrder = (int) (((clone $base)->min('order')) ?? 0);
        $maxOrder = (int) (((clone $base)->max('order')) ?? 0);

        $exerciseLevels = (clone $base)
            ->orderByRaw('CASE WHEN `order` IS NULL THEN 1 ELSE 0 END, `order` ASC')
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.tenant.exercises.exercise-levels.index', [
            'exerciseLevels' => $exerciseLevels,
            'minOrder' => $minOrder,
            'maxOrder' => $maxOrder,
        ]);
    }
}
