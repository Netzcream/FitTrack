<?php

namespace App\Livewire\Tenant\Manuals;

use App\Models\Central\Manual;
use App\Enums\ManualCategory;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('components.layouts.tenant')]
class ManualsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Manual::query()
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('media');

        // Búsqueda por título o resumen
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('summary', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro por categoría
        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        $manuals = $query->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('livewire.tenant.manuals.index', [
            'manuals' => $manuals,
            'categories' => ManualCategory::options(),
        ]);
    }
}
