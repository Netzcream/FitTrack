<?php

namespace App\Livewire\Tenant\Exercises\Exercises;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Exercise\Exercise;
use App\Models\Tenant\Exercise\ExerciseLevel;
use App\Models\Tenant\Exercise\MovementPattern;
use App\Models\Tenant\Exercise\ExercisePlane;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $status = '';
    public ?int $exercise_level_id = null;
    public ?int $movement_pattern_id = null;
    public ?int $exercise_plane_id = null;
    public ?string $unilateral = '';   // '', '1', '0'
    public ?string $external_load = ''; // '', '1', '0'
    public ?string $default_modality = '';

    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;

    public ?int $exerciseToDelete = null;

    public function updated($field): void
    {
        if (in_array($field, [
            'search','status','exercise_level_id','movement_pattern_id','exercise_plane_id',
            'unilateral','external_load','default_modality','perPage'
        ], true)) {
            $this->resetPage();
        }
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->exerciseToDelete = $id;
    }

    public function delete(): void
    {
        if (!$this->exerciseToDelete) return;
        if ($exercise = Exercise::find($this->exerciseToDelete)) {
            $exercise->delete(); // SoftDeletes
        }
        $this->dispatch('exercise-deleted');
        $this->reset('exerciseToDelete');
    }

    public function render()
    {
        $base = Exercise::query()
            ->when($this->search !== '', fn($q) =>
                $q->where(fn($qq) =>
                    $qq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('code', 'like', "%{$this->search}%")
                )
            )
            ->when($this->status !== null && $this->status !== '', fn($q) =>
                $q->where('status', $this->status)
            )
            ->when($this->exercise_level_id, fn($q) =>
                $q->where('exercise_level_id', $this->exercise_level_id)
            )
            ->when($this->movement_pattern_id, fn($q) =>
                $q->where('movement_pattern_id', $this->movement_pattern_id)
            )
            ->when($this->exercise_plane_id, fn($q) =>
                $q->where('exercise_plane_id', $this->exercise_plane_id)
            )
            ->when($this->unilateral === '1' || $this->unilateral === '0', fn($q) =>
                $q->where('unilateral', $this->unilateral === '1')
            )
            ->when($this->external_load === '1' || $this->external_load === '0', fn($q) =>
                $q->where('external_load', $this->external_load === '1')
            )
            ->when($this->default_modality !== null && $this->default_modality !== '', fn($q) =>
                $q->where('default_modality', $this->default_modality)
            )
            ->with(['level:id,name', 'pattern:id,name', 'plane:id,name']);

        $exercises = (clone $base)
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.exercises.exercises.index', [
            'exercises' => $exercises,
            'levels'    => ExerciseLevel::orderBy('name')->get(['id','name']),
            'patterns'  => MovementPattern::orderBy('name')->get(['id','name']),
            'planes'    => ExercisePlane::orderBy('name')->get(['id','name']),
        ]);
    }
}
