<?php

namespace App\Livewire\Central\Contact;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Central\Contact;

class Index extends Component
{
    use WithPagination;
    public ?Contact $viewing = null;

    /** @var array<string> */
    public array $selected = [];

    public bool $selectPage = false;
    public bool $selectAll = false;
    public ?string $deletingId = null;
    public ?int $noLeidos = 0;

    protected $listeners = [
        'refreshContacts' => '$refresh',
    ];

    public function mount()
    {
        $this->noLeidos = Contact::unread()->count();
    }

    // ---------------------------------------------------------------------
    // Helpers selección
    // ---------------------------------------------------------------------

    public function confirmDeleteAsk(string $id): void
    {
        $this->deletingId = $id;
    }
    public function deleteConfirmed(): void
    {
        if ($this->deletingId && ($c = Contact::find($this->deletingId))) {
            $c->delete();
            session()->flash('message', __('site.deleted_successfully'));
        }

        $this->deletingId = null;

        $this->dispatch('contact-deleted');

        $this->dispatch('refreshContacts');
    }


    public function confirmBulkDeleteAsk(): void
    {
        if (empty($this->selected)) return;
    }

    public function deleteSelectedConfirmed(): void
    {
        if (empty($this->selected)) return;

        Contact::whereIn('id', $this->selected)->delete();
        $this->clearSelection();

        session()->flash('message', __('site.deleted_successfully'));

        // Cierra por listener
        $this->dispatch('contacts-deleted');

        // Refrescar tabla
        $this->dispatch('refreshContacts');
    }

    /** Abre el modal, setea el contacto y marca como leído */
    public function openAndMark(string $id): void
    {
        $contact = Contact::findOrFail($id);

        if ($contact->unread) {
            $contact->markAsRead();
        }

        $this->viewing = $contact;

        // Abre el Flux Modal por evento del navegador
        $this->dispatch('modal-open', name: 'view-contact');

        // refresco listado (para quitar highlight de no leído)
        $this->dispatch('refreshContacts');
    }


    public function updatedSelectPage($value)
    {
        if ($value) {
            $this->selected = $this->currentPageContactIds();
        } else {
            $this->reset(['selected', 'selectAll']);
        }
    }

    public function selectAll()
    {
        $this->selectAll = true;
        $this->selected = Contact::pluck('id')->map(fn($id) => (string)$id)->toArray();
    }

    public function clearSelection()
    {
        $this->reset(['selected', 'selectPage', 'selectAll']);
    }

    /** @return array<string> */
    protected function currentPageContactIds(): array
    {
        // Respetamos la página actual del paginador de Livewire
        $page = (int)($this->page ?? 1);

        return Contact::orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page)
            ->pluck('id')
            ->map(fn($id) => (string)$id)
            ->toArray();
    }

    // ---------------------------------------------------------------------
    // Acciones individuales
    // ---------------------------------------------------------------------
    public function toggleRead(string $id)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return;
        }

        if ($contact->unread) {
            $contact->markAsRead();
            session()->flash('message', __('site.marked_as_read'));
        } else {
            $contact->markAsUnread();
            session()->flash('message', __('site.marked_as_unread'));
        }

        $this->dispatch('refreshContacts');
    }

    public function confirmDelete(string $id)
    {
        $contact = Contact::find($id);

        if ($contact) {
            $contact->delete();
            session()->flash('message', __('site.deleted_successfully'));
            $this->clearSelection();
            $this->dispatch('refreshContacts');
        }
    }

    // ---------------------------------------------------------------------
    // Acciones bulk
    // ---------------------------------------------------------------------
    public function markSelectedAsRead()
    {
        if (empty($this->selected)) return;

        Contact::whereIn('id', $this->selected)->update(['unread' => false]);
        session()->flash('message', __('site.marked_as_read_bulk'));
        $this->clearSelection();
        $this->dispatch('refreshContacts');
    }

    public function markSelectedAsUnread()
    {
        if (empty($this->selected)) return;

        Contact::whereIn('id', $this->selected)->update(['unread' => true]);
        session()->flash('message', __('site.marked_as_unread_bulk'));
        $this->clearSelection();
        $this->dispatch('refreshContacts');
    }

    public function deleteSelected()
    {
        if (empty($this->selected)) return;

        Contact::whereIn('id', $this->selected)->delete();
        session()->flash('message', __('site.deleted_successfully'));
        $this->clearSelection();
        $this->dispatch('refreshContacts');
    }

    // Si cambias de página, reseteo selección de página (opcional)
    public function updatingPage()
    {
        $this->reset(['selectPage', 'selectAll', 'selected']);
    }


    public function render()
    {
        $contacts = Contact::orderBy('created_at', 'desc')->paginate(5);
        return view('livewire.central.contact.index', [
            'contacts' => $contacts,
            'totalMatching' => Contact::count(),
        ]);
    }
}
