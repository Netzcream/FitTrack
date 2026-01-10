<?php

namespace App\Livewire\Tenant\Messages;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Services\Tenant\MessagingService;
use App\Enums\ParticipantType;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.tenant', [
    'title' => 'Mensajes',
])]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $selectedStudentId = null;

    public function updated($field): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search']);
        $this->resetPage();
    }

    public function startConversation()
    {
        $this->validate([
            'selectedStudentId' => 'required|exists:students,id',
        ]);

        $messagingService = app(MessagingService::class);

        $conversation = $messagingService->findOrCreateConversation($this->selectedStudentId);

        $this->reset('selectedStudentId');

        return redirect()->route('tenant.dashboard.messages.conversations.show', $conversation);
    }

    public function render()
    {
        $messagingService = app(MessagingService::class);
        /** @var User $user */
        $user = Auth::user();

        /** @var \Illuminate\Pagination\LengthAwarePaginator $conversations */
        $conversations = $messagingService->getConversations(
            ParticipantType::TENANT,
            $user->id,
            15
        );

        // Get students without active conversation for the select
        $conversationStudentIds = $conversations->pluck('student_id')->filter();

        $students = \App\Models\Tenant\Student::whereNotIn('id', $conversationStudentIds)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return view('livewire.tenant.messages.index', [
            'conversations' => $conversations,
            'students' => $students,
        ]);
    }
}
