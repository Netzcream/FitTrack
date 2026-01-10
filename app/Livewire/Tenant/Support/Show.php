<?php

namespace App\Livewire\Tenant\Support;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\Central\MessagingService;
use App\Models\Central\Conversation;
use App\Enums\ParticipantType;

#[Layout('components.layouts.tenant')]
class Show extends Component
{
    public string $newMessage = '';
    public ?Conversation $conversation = null;

    public function mount()
    {
        $tenantId = tenant('id'); // String ID (slug)

        $messagingService = app(MessagingService::class);
        $this->conversation = $messagingService->findOrCreateConversation($tenantId, 'Soporte técnico');
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
        ]);

    $tenantId = tenant('id'); // String ID (slug)

        $messagingService = app(MessagingService::class);
        $messagingService->sendMessage(
            $this->conversation->id,
            ParticipantType::TENANT,
            $tenantId,
            $this->newMessage
        );

        $this->reset('newMessage');
        $this->dispatch('message-sent');
    }

    public function render()
    {
        $tenantId = tenant('id');
        $messagingService = app(MessagingService::class);

        // Mark as read while viewing
        $messagingService->markAsRead(
            $this->conversation->id,
            ParticipantType::TENANT,
            $tenantId
        );

        $messages = $messagingService->getMessages($this->conversation->id, 50);

        return view('livewire.tenant.support.show', [
            'messages' => $messages,
        ]);
    }
}
