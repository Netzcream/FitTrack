<?php

namespace App\Livewire\Central;

use Livewire\Component;
use App\Models\Central\Conversation;
use App\Enums\ParticipantType;

class SupportBadgeNavItem extends Component
{
    public int $unreadCount = 0;

    public function mount()
    {
        $this->updateCount();
    }

    #[\Livewire\Attributes\Computed]
    public function getUnreadCount()
    {
        $conversations = Conversation::query()
            ->whereHas('participants', function ($query) {
                $query->where('participant_id', 1)
                      ->where('participant_type', ParticipantType::CENTRAL->value);
            })
            ->withUnreadCount(ParticipantType::CENTRAL->value, 1)
            ->get();

        return $conversations->sum('unread_count');
    }

    private function updateCount()
    {
        $this->unreadCount = $this->getUnreadCount();
    }

    #[\Livewire\Attributes\On('message-sent')]
    public function refreshUnreadCount(): void
    {
        $this->updateCount();
    }

    public function render()
    {
        $this->updateCount();

        return view('livewire.central.support-badge-nav-item');
    }
}
