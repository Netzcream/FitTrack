<?php

namespace App\Livewire\Central\Contact;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Central\Contact;

class Index extends Component
{
    use WithPagination;

    public ?Contact $viewing = null;
    public array $selected = [];

    public bool $selectPage = false;
    public bool $selectAll = false;
    public ?string $deletingId = null;
    public ?int $noLeidos = 0;

    // Filtro de búsqueda
    public string $q = '';

    protected $listeners = ['refreshContacts' => '$refresh'];

    public function mount(): void
    {
        $this->noLeidos = Contact::unread()->count();
    }

    // ---------------------------------------------------------------------
    // Selección y acciones bulk
    // ---------------------------------------------------------------------

    public function updatedSelectPage($value): void
    {
        if ($value) {
            $this->selected = $this->currentPageContactIds();
        } else {
            $this->reset(['selected', 'selectAll']);
        }
    }

    public function selectAll(): void
    {
        $this->selectAll = true;
        $this->selected = Contact::pluck('id')->map(fn($id) => (string)$id)->toArray();
    }

    public function clearSelection(): void
    {
        $this->reset(['selected', 'selectPage', 'selectAll']);
    }

    /** IDs de la página actual */
    protected function currentPageContactIds(): array
    {
        $page = (int)($this->page ?? 1);
        return Contact::orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page)
            ->pluck('id')
            ->map(fn($id) => (string)$id)
            ->toArray();
    }

    // ---------------------------------------------------------------------
    // CRUD y estados
    // ---------------------------------------------------------------------

    public function openAndMark(string $id): void
    {
        $contact = Contact::findOrFail($id);

        if ($contact->unread) {
            $contact->markAsRead();
        }

        $this->viewing = $contact;
        $this->dispatch('modal-open', name: 'view-contact');
        $this->dispatch('refreshContacts');
    }

    public function toggleRead(string $id): void
    {
        $contact = Contact::find($id);
        if (!$contact) return;

        if ($contact->unread) {
            $contact->markAsRead();
            session()->flash('message', __('common.marked_as_read'));
        } else {
            $contact->markAsUnread();
            session()->flash('message', __('common.marked_as_unread'));
        }

        $this->dispatch('refreshContacts');
    }

    public function confirmDeleteAsk(string $id): void
    {
        $this->deletingId = $id;
    }

    public function deleteConfirmed(): void
    {
        if ($this->deletingId && ($c = Contact::find($this->deletingId))) {
            $c->delete();
            session()->flash('message', __('common.deleted_successfully'));
        }

        $this->deletingId = null;
        $this->dispatch('contact-deleted');
        $this->dispatch('refreshContacts');
    }

    // ---------------------------------------------------------------------
    // Acciones masivas
    // ---------------------------------------------------------------------

    public function markSelectedAsRead(): void
    {
        if (empty($this->selected)) return;
        Contact::whereIn('id', $this->selected)->update(['unread' => false]);
        session()->flash('message', __('common.marked_as_read_bulk'));
        $this->clearSelection();
        $this->dispatch('refreshContacts');
    }

    public function markSelectedAsUnread(): void
    {
        if (empty($this->selected)) return;
        Contact::whereIn('id', $this->selected)->update(['unread' => true]);
        session()->flash('message', __('common.marked_as_unread_bulk'));
        $this->clearSelection();
        $this->dispatch('refreshContacts');
    }

    public function deleteSelectedConfirmed(): void
    {
        if (empty($this->selected)) return;
        Contact::whereIn('id', $this->selected)->delete();
        session()->flash('message', __('common.deleted_successfully'));
        $this->clearSelection();
        $this->dispatch('contacts-deleted');
        $this->dispatch('refreshContacts');
    }

    public function updatingPage(): void
    {
        $this->reset(['selectPage', 'selectAll', 'selected']);
    }

    // ---------------------------------------------------------------------
    // Render
    // ---------------------------------------------------------------------
    public function render()
    {
        $contacts = Contact::query()
            ->when($this->q, fn($q) => $q->where(function ($qq) {
                $t = "%{$this->q}%";
                $qq->where('name', 'like', $t)
                   ->orWhere('email', 'like', $t)
                   ->orWhere('phone', 'like', $t);
            }))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.central.contact.index', [
            'contacts' => $contacts,
            'totalMatching' => Contact::count(),
        ]);
    }
}
