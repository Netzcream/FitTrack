<?php

namespace App\Livewire\Tenant;

use Livewire\Component;
use App\Models\Tenant\Conversation;
use App\Enums\ParticipantType;
use Illuminate\Support\Facades\Auth;

class ConversationBadgeNavItem extends Component
{
    public int $unreadCount = 0;

    public function mount()
    {
        $this->updateCount();
    }

    #[\Livewire\Attributes\Computed]
    public function getUnreadCount()
    {
        $user = Auth::user();
        if (!$user) {
            return 0;
        }

        return Conversation::query()
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('participant_id', $user->id)
                      ->where('participant_type', ParticipantType::TENANT->value);
            })
            ->withUnreadCount(ParticipantType::TENANT->value, $user->id)
            ->get()
            ->sum('unread_count');
    }

    private function updateCount()
    {
        $this->unreadCount = $this->getUnreadCount();
    }

    public function render()
    {
        $this->updateCount();

        return view('livewire.tenant.conversation-badge-nav-item');
    }
}
