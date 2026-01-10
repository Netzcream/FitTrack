<?php

namespace App\Livewire\Tenant;

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
        $tenantId = tenant('id');
        if (!$tenantId) {
            return 0;
        }

        // Use tenant_id on conversations to include threads even if
        // a tenant participant row hasn't been created yet.
        $conversations = Conversation::query()
            ->forTenant($tenantId)
            ->withUnreadCount(ParticipantType::TENANT->value, $tenantId)
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

        return view('livewire.tenant.support-badge-nav-item');
    }
}
