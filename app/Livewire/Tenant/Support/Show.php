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

    public function markAsRead()
    {
        $tenantId = tenant('id');
        $messagingService = app(MessagingService::class);

        $messagingService->markAsRead(
            $this->conversation->id,
            ParticipantType::TENANT,
            $tenantId
        );
    }

    public function render()
    {
        $tenantId = tenant('id');
        $messagingService = app(MessagingService::class);

        $messages = $messagingService->getMessages($this->conversation->id, 50);

        // Find first unread message ID
        $firstUnreadMessageId = $this->getFirstUnreadMessageId($tenantId, ParticipantType::TENANT);

        return view('livewire.tenant.support.show', [
            'messages' => $messages,
            'firstUnreadMessageId' => $firstUnreadMessageId,
        ]);
    }

    private function getFirstUnreadMessageId(string $participantId, ParticipantType $participantType): ?int
    {
        $participant = \App\Models\Central\ConversationParticipant::where('conversation_id', $this->conversation->id)
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->first();

        if (!$participant || !$participant->last_read_at) {
            // If never read, first message is the first unread
            return \App\Models\Central\Message::where('conversation_id', $this->conversation->id)
                ->orderBy('created_at', 'asc')
                ->value('id');
        }

        return \App\Models\Central\Message::where('conversation_id', $this->conversation->id)
            ->where('created_at', '>', $participant->last_read_at)
            ->orderBy('created_at', 'asc')
            ->value('id');
    }
}
