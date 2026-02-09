<?php

namespace App\Livewire\Tenant\Messages;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Services\Tenant\MessagingService;
use App\Models\Tenant\Conversation;
use App\Enums\ParticipantType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

#[Layout('components.layouts.tenant', [
    'title' => 'Conversación',
])]
class Show extends Component
{
    use WithPagination;

    public int $conversationId;
    public string $newMessage = '';
    public Conversation $conversation;

    public function mount(Conversation $conversation)
    {
        $this->conversation = $conversation->load([
            'student.currentPlanAssignment.plan',
            'student.commercialPlan',
            'participants'
        ]);
        $this->conversationId = $conversation->id;

        Gate::authorize('view', $this->conversation);
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
        ]);

        /** @var User $user */
        $user = Auth::user();

        Gate::authorize('sendMessage', $this->conversation);

        $messagingService = app(MessagingService::class);
        $messagingService->sendMessage(
            $this->conversationId,
            ParticipantType::TENANT,
            $user->id,
            $this->newMessage
        );

        $this->reset('newMessage');
        $this->dispatch('message-sent');
    }

    public function render()
    {
        $messagingService = app(MessagingService::class);
        $tenantParticipantId = MessagingService::TENANT_PARTICIPANT_ID;

        $messages = $messagingService->getMessages($this->conversationId, 50);

        // Find first unread message ID BEFORE marking as read
        $firstUnreadMessageId = $this->getFirstUnreadMessageId($tenantParticipantId, ParticipantType::TENANT);

        // Mark as read while viewing
        $messagingService->markAsRead(
            $this->conversationId,
            ParticipantType::TENANT,
            $tenantParticipantId
        );

        return view('livewire.tenant.messages.show', [
            'messages' => $messages,
            'firstUnreadMessageId' => $firstUnreadMessageId,
        ]);
    }

    private function getFirstUnreadMessageId(string|int $participantId, ParticipantType $participantType): ?int
    {
        $participant = \App\Models\Tenant\ConversationParticipant::where('conversation_id', $this->conversationId)
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->first();

        if (!$participant || !$participant->last_read_at) {
            // If never read, first message is the first unread
            return \App\Models\Tenant\Message::where('conversation_id', $this->conversationId)
                ->orderBy('created_at', 'asc')
                ->value('id');
        }

        return \App\Models\Tenant\Message::where('conversation_id', $this->conversationId)
            ->where('created_at', '>', $participant->last_read_at)
            ->orderBy('created_at', 'asc')
            ->value('id');
    }
}
