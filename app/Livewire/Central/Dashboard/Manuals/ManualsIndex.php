<?php

namespace App\Livewire\Central\Dashboard\Manuals;

use App\Enums\ManualCategory;
use App\Models\Central\Manual;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('components.layouts.app')]
class ManualsIndex extends Component
{
    use WithPagination;

    public string $sortBy = 'sort_order';
    public string $sortDirection = 'asc';
    public string $search = '';
    public string $categoryFilter = '';
    public ?string $manualToDelete = null;

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updated($field): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->resetPage();
    }

    #[Computed]
    public function manuals()
    {
        return Manual::query()
            ->with('media') // Eager load media para optimizar consultas
            ->when($this->search, function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('title', 'like', $term)
                       ->orWhere('summary', 'like', $term)
                       ->orWhere('content', 'like', $term);
                });
            })
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);
    }

    #[Computed]
    public function categories()
    {
        return ManualCategory::options();
    }

    public function confirmDelete(string $uuid): void
    {
        $this->manualToDelete = $uuid;
        $this->dispatch('modal-open', name: 'confirm-delete-manual');
    }

    public function delete(): void
    {
        if (!$this->manualToDelete) {
            return;
        }

        try {
            $manual = Manual::where('uuid', $this->manualToDelete)->firstOrFail();
            $manual->delete();

            $this->dispatch('notify', message: __('manuals.deleted_success'), type: 'success');
            $this->dispatch('manual-deleted');
            $this->reset('manualToDelete');
        } catch (\Throwable $e) {
            $message = __('manuals.error_delete', ['error' => $e->getMessage()]);
            $this->dispatch('notify', message: $message, type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.central.dashboard.manuals.index');
    }
}
