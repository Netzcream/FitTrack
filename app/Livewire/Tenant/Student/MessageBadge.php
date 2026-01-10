<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use App\Models\Tenant\Conversation;
use App\Enums\ParticipantType;
use Illuminate\Support\Facades\Auth;

class MessageBadge extends Component
{
    public int $unreadCount = 0;

    public function mount()
    {
        $this->updateCount();
    }

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

    private function updateCount()
    {
        $this->unreadCount = $this->getUnreadCount();
    }

    public function render()
    {
        $this->updateCount();

        return view('livewire.tenant.student.message-badge');
    }
}
