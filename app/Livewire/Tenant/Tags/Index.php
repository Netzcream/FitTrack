<?php

namespace App\Livewire\Tenant\Tags;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Tag;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public string $search = '';
    public $active = null; // "yes" | "no" | null
    public int $perPage = 10;

    public ?int $tagToDelete = null;

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function filter(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->tagToDelete = $id;
    }

    public function delete(): void
    {
        if (!$this->tagToDelete) return;

        if ($tag = Tag::find($this->tagToDelete)) {
            $tag->delete(); // soft delete
        }

        $this->dispatch('tag-deleted');
        $this->reset('tagToDelete');
    }

    public function render()
    {
        $tags = Tag::query()
            ->when($this->search !== '', fn($q) =>
                $q->where(fn($qq) =>
                    $qq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('code', 'like', "%{$this->search}%")
                )
            )
            ->when(!empty($this->active), fn($q) =>
                $q->where('is_active', $this->active === 'yes')
            )
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.tags.index', compact('tags'));
    }
}
