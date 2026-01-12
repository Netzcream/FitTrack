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
        $this->conversation = $conversation->load([
            'tenant.plan',
            'tenant.config',
        ]);
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

    public function markAsRead()
    {
        $messagingService = app(MessagingService::class);

        $messagingService->markAsRead(
            $this->conversation->id,
            ParticipantType::CENTRAL,
            1
        );
    }

    public function render()
    {
        $messagingService = app(MessagingService::class);

        $messages = $messagingService->getMessages($this->conversation->id, 50);

        // Find first unread message ID
        $firstUnreadMessageId = $this->getFirstUnreadMessageId(1, ParticipantType::CENTRAL);

        // Get tenant stats
        $tenantStats = $this->getTenantStats();

        return view('livewire.central.support.show', [
            'messages' => $messages,
            'firstUnreadMessageId' => $firstUnreadMessageId,
            'tenantStats' => $tenantStats,
        ]);
    }

    private function getTenantStats(): array
    {
        $tenant = $this->conversation->tenant;

        if (!$tenant) {
            return [
                'totalStudents' => 0,
                'activeStudents' => 0,
                'inactiveStudents' => 0,
                'prospectStudents' => 0,
            ];
        }

        // Switch to tenant context to query student data
        $stats = ['totalStudents' => 0, 'activeStudents' => 0, 'inactiveStudents' => 0, 'prospectStudents' => 0];

        try {
            $tenant->run(function () use (&$stats) {
                $stats['totalStudents'] = \App\Models\Tenant\Student::count();
                $stats['activeStudents'] = \App\Models\Tenant\Student::where('status', 'active')->count();
                $stats['inactiveStudents'] = \App\Models\Tenant\Student::where('status', 'inactive')->count();
                $stats['prospectStudents'] = \App\Models\Tenant\Student::where('status', 'prospect')->count();
            });
        } catch (\Exception $e) {
            // If there's an error, return zeros
        }

        return $stats;
    }

    private function getFirstUnreadMessageId(int $participantId, ParticipantType $participantType): ?int
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
