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
            ->withUnreadCount(ParticipantType::STUDENT->value, $student->id)
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

        /** @var User $user */
        $user = Auth::user();
        $student = $user->student;

        $messages = $messagingService->getMessages($this->conversation->id, 50);

        // Find first unread message ID BEFORE marking as read
        $firstUnreadMessageId = $this->getFirstUnreadMessageId($student->id, ParticipantType::STUDENT);

        // Mark as read while viewing
        $messagingService->markAsRead(
            $this->conversation->id,
            ParticipantType::STUDENT,
            $student->id
        );

        return view('livewire.tenant.student.messages', [
            'messages' => $messages,
            'student' => $student,
            'firstUnreadMessageId' => $firstUnreadMessageId,
        ]);
    }

    private function getFirstUnreadMessageId(int $participantId, ParticipantType $participantType): ?int
    {
        $participant = \App\Models\Tenant\ConversationParticipant::where('conversation_id', $this->conversation->id)
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->first();

        if (!$participant || !$participant->last_read_at) {
            // If never read, first message is the first unread
            return \App\Models\Tenant\Message::where('conversation_id', $this->conversation->id)
                ->orderBy('created_at', 'asc')
                ->value('id');
        }

        return \App\Models\Tenant\Message::where('conversation_id', $this->conversation->id)
            ->where('created_at', '>', $participant->last_read_at)
            ->orderBy('created_at', 'asc')
            ->value('id');
    }
}
