<?php

namespace App\Livewire\Tenant\Contacts;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Contact;

#[Layout('components.layouts.tenant')]
class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public string $search = '';
    public int $perPage = 10;
    public ?string $contactToDelete = null;

    protected array $allowedSorts = ['name', 'email', 'created_at'];

    /* ---------------------------- Ordenamiento ---------------------------- */
    public function sort(string $column): void
    {
        if (! in_array($column, $this->allowedSorts)) return;

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    /* ---------------------------- Filtros ---------------------------- */
    public function updating($field): void
    {
        if ($field === 'search') {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    /* ---------------------------- Eliminación ---------------------------- */
    public function confirmDelete(string $uuid): void
    {
        $this->contactToDelete = $uuid;
    }

    public function delete(): void
    {
        $contact = Contact::where('uuid', $this->contactToDelete)->first();
        if ($contact) {
            $contact->delete();
            $this->dispatch('contact-deleted');
        }
        $this->reset('contactToDelete');
    }

    /* ---------------------------- Render ---------------------------- */
    public function render()
    {
        $contacts = Contact::query()
            ->when($this->search, function ($query) {
                $query->where(fn($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('mobile', 'like', "%{$this->search}%")
                    ->orWhere('message', 'like', "%{$this->search}%"));
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.contacts.index', compact('contacts'));
    }
}
