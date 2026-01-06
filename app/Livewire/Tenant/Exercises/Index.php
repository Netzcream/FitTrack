<?php

namespace App\Livewire\Tenant\Exercises;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Exercise;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = ''; // activo/inactivo
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public ?string $deleteUuid = null;

    protected $paginationTheme = 'tailwind';

    /* ------------------------- Reglas reactivas ------------------------- */
    public function updating($field)
    {
        if (in_array($field, ['search', 'status'])) {
            $this->resetPage();
        }
    }

    /* ------------------------- Acciones ------------------------- */
    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
    }

    public function confirmDelete(string $uuid): void
    {
        $this->deleteUuid = $uuid;
    }

    public function delete(): void
    {
        if (!$this->deleteUuid) return;

        $exercise = Exercise::where('uuid', $this->deleteUuid)->first();
        if ($exercise) {
            $exercise->delete();
        }

        $this->dispatch('exercise-deleted');
        $this->deleteUuid = null;
    }

    /* ------------------------- Render ------------------------- */
    public function render()
    {
        $query = Exercise::query()
            ->search($this->search)
            ->when($this->status !== '', fn (Builder $q) =>
                $q->where('is_active', (bool) $this->status))
            ->orderBy($this->sortBy, $this->sortDirection);

        return view('livewire.tenant.exercises.index', [
            'exercises' => $query->paginate(10),
        ]);
    }
}
