<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\Tenant\MessagingService;
use App\Models\Tenant\Conversation;
use App\Enums\ParticipantType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

#[Layout('layouts.student')]
class Messages extends Component
{
    public string $newMessage = '';
    public ?Conversation $conversation = null;
    public int $unreadCount = 0;

    #[\Livewire\Attributes\Computed]
    public function getUnreadCount()
    {

        /** @var User $user */
        $user = Auth::user();

        $student = $user->student;
        if (!$student) {
            return 0;
        }

        return Conversation::query()
            ->whereHas('participants', function ($query) use ($student) {
                $query->where('participant_id', $student->id)
                    ->where('participant_type', ParticipantType::STUDENT->value);
            })
            ->withUnreadCount($student->id, ParticipantType::STUDENT)
            ->get()
            ->sum('unread_count');
    }

    public function mount()
    {
        $messagingService = app(MessagingService::class);
        /** @var User $user */
        $user = Auth::user();
        $student = $user->student;

        // Find or create conversation for this student
        $this->conversation = $messagingService->findOrCreateConversation($student->id);
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $student = $user->student;

        $messagingService = app(MessagingService::class);
        $messagingService->sendMessage(
            $this->conversation->id,
            ParticipantType::STUDENT,
            $student->id,
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
        $student = $user->student;
        $messagingService->markAsRead(
            $this->conversation->id,
            ParticipantType::STUDENT,
            $student->id
        );

        $messages = $messagingService->getMessages($this->conversation->id, 50);

        return view('livewire.tenant.student.messages', [
            'messages' => $messages,
        ]);
    }
}
