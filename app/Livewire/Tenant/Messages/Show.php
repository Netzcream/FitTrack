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

        // Mark as read while viewing
        /** @var User $user */
        $user = Auth::user();
        $messagingService->markAsRead(
            $this->conversationId,
            ParticipantType::TENANT,
            $user->id
        );

        $messages = $messagingService->getMessages($this->conversationId, 50);

        return view('livewire.tenant.messages.show', [
            'messages' => $messages,
        ]);
    }
}
