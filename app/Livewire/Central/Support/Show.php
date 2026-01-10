<?php

namespace App\Livewire\Central\Support;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\Central\MessagingService;
use App\Models\Central\Conversation;
use App\Enums\ParticipantType;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public string $newMessage = '';
    public Conversation $conversation;

    public function mount(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
        ]);

        $messagingService = app(MessagingService::class);
        $messagingService->sendMessage(
            $this->conversation->id,
            ParticipantType::CENTRAL,
            1,
            $this->newMessage
        );

        $this->reset('newMessage');
        $this->dispatch('message-sent');
    }

    public function render()
    {
        $messagingService = app(MessagingService::class);

        // Mark as read while viewing
        $messagingService->markAsRead(
            $this->conversation->id,
            ParticipantType::CENTRAL,
            1
        );

        $messages = $messagingService->getMessages($this->conversation->id, 50);

        return view('livewire.central.support.show', [
            'messages' => $messages,
        ]);
    }
}
