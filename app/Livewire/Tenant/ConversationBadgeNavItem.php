<?php

namespace App\Livewire\Tenant;

use Livewire\Component;
use App\Enums\ParticipantType;
use App\Services\Tenant\MessagingService;

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
        $tenantId = tenant('id');
        if (!$tenantId) {
            return 0;
        }

        $messagingService = app(MessagingService::class);

        return $messagingService->getUnreadCount(ParticipantType::TENANT, (string) $tenantId);
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
