<?php

namespace App\Livewire\Central\Support;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Central\Conversation;
use App\Enums\ParticipantType;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updated($field): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search']);
        $this->resetPage();
    }

    public function render()
    {
        $conversations = Conversation::query()
            ->with(['tenant'])
            ->has('messages')
            ->whereHas('tenant') // Solo conversaciones con tenant válido
            ->withUnreadCount(ParticipantType::CENTRAL->value, 1) // Conteo de no leídos para Central
            ->when($this->search, function ($q) {
                $t = "%{$this->search}%";
                $q->whereHas('tenant', fn ($qq) => $qq->where('name', 'like', $t))
                  ->orWhere('tenant_id', 'like', $t);
            })
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('livewire.central.support.index', [
            'conversations' => $conversations,
        ]);
    }
}
