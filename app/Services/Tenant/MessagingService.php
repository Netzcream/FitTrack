<?php

namespace App\Services\Tenant;

use App\Models\User;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationParticipant;
use App\Models\Tenant\Message;
use App\Enums\ConversationType;
use App\Enums\ParticipantType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessagingService
{
    /**
     * Find or create a conversation between Tenant and a Student
     */
    public function findOrCreateConversation(int $studentId, ?string $subject = null): Conversation
    {
        return DB::transaction(function () use ($studentId, $subject) {
            /** @var User|null $user */
            $user = Auth::user();

            // Check if conversation already exists
            $conversation = Conversation::where('type', ConversationType::TENANT_STUDENT)
                ->where('student_id', $studentId)
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'type' => ConversationType::TENANT_STUDENT,
                    'student_id' => $studentId,
                    'subject' => $subject,
                ]);
            }

            $this->ensureConversationParticipants($conversation, $studentId, $user);

            return $conversation->fresh(['participants', 'student']);
        });
    }

    /**
     * Send a message in a conversation
     */
    public function sendMessage(
        int $conversationId,
        ParticipantType $senderType,
        int $senderId,
        string $body,
        ?array $attachments = null
    ): Message {
        return DB::transaction(function () use ($conversationId, $senderType, $senderId, $body, $attachments) {
            $conversation = Conversation::findOrFail($conversationId);

            if ($conversation->type === ConversationType::TENANT_STUDENT && $conversation->student_id) {
                $this->ensureConversationParticipants(
                    $conversation,
                    (int) $conversation->student_id,
                    Auth::user()
                );
            }

            if ($senderType === ParticipantType::TENANT) {
                ConversationParticipant::firstOrCreate([
                    'conversation_id' => $conversation->id,
                    'participant_type' => ParticipantType::TENANT,
                    'participant_id' => (string) $senderId,
                ]);
            }

            $message = $conversation->addMessage(
                $senderType->value,
                $senderId,
                $body,
                $attachments
            );

            // Mark as read for sender (their own messages don't count as unread)
            $this->markAsRead($conversationId, $senderType, $senderId);

            // Fire event for notifications
            event(new \App\Events\Tenant\MessageSent($message));

            return $message;
        });
    }

    /**
     * Mark conversation as read for a participant
     */
    public function markAsRead(int $conversationId, ParticipantType $participantType, int $participantId): void
    {
        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->update(['last_read_at' => now()]);
    }

    /**
     * Get conversations for a participant
     */
    public function getConversations(
        ParticipantType $participantType,
        int $participantId,
        int $perPage = 15
    ) {
        if ($participantType === ParticipantType::TENANT) {
            /** @var User|null $actor */
            $actor = Auth::user();

            if ($actor && (int) $actor->id === $participantId) {
                $this->repairBrokenStudentConversationsForTenant($actor);
            }
        }

        $conversationIds = ConversationParticipant::where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->pluck('conversation_id');

        return Conversation::whereIn('id', $conversationIds)
            ->with(['lastMessage', 'participants', 'student'])
            ->withUnreadCount($participantType->value, $participantId)
            ->orderByDesc('last_message_at')
            ->paginate($perPage);
    }

    /**
     * Get messages from a conversation
     */
    public function getMessages(int $conversationId, int $perPage = 50)
    {
        return Message::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get unread messages count for a participant
     */
    public function getUnreadCount(ParticipantType $participantType, int $participantId): int
    {
        $conversationIds = ConversationParticipant::where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->pluck('conversation_id');

        return Message::whereIn('conversation_id', $conversationIds)
            ->whereRaw('(messages.created_at > COALESCE((
                SELECT last_read_at
                FROM conversation_participants
                WHERE conversation_id = messages.conversation_id
                AND participant_type = ?
                AND participant_id = ?
            ), "1970-01-01 00:00:00"))', [$participantType->value, $participantId])
            ->count();
    }

    /**
     * Mute/unmute a conversation
     */
    public function toggleMute(int $conversationId, ParticipantType $participantType, int $participantId, bool $mute): void
    {
        $participant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->firstOrFail();

        if ($mute) {
            $participant->mute();
        } else {
            $participant->unmute();
        }
    }

    /**
     * Delete a conversation (soft delete)
     */
    public function deleteConversation(int $conversationId): void
    {
        $conversation = Conversation::findOrFail($conversationId);
        $conversation->delete();
    }

    /**
     * Get conversation by student (helper for student context)
     */
    public function getConversationForStudent(int $studentId): ?Conversation
    {
        $conversation = Conversation::where('type', ConversationType::TENANT_STUDENT)
            ->where('student_id', $studentId)
            ->first();

        if (!$conversation) {
            return null;
        }

        /** @var User|null $user */
        $user = Auth::user();
        $this->ensureConversationParticipants($conversation, $studentId, $user);

        return $conversation;
    }

    private function ensureConversationParticipants(Conversation $conversation, int $studentId, ?User $actor): void
    {
        ConversationParticipant::firstOrCreate([
            'conversation_id' => $conversation->id,
            'participant_type' => ParticipantType::STUDENT,
            'participant_id' => (string) $studentId,
        ]);

        $tenantParticipantIds = $this->resolveTenantParticipantIds($conversation, $actor);

        foreach ($tenantParticipantIds as $tenantParticipantId) {
            ConversationParticipant::firstOrCreate([
                'conversation_id' => $conversation->id,
                'participant_type' => ParticipantType::TENANT,
                'participant_id' => $tenantParticipantId,
            ]);
        }

        if (empty($tenantParticipantIds)) {
            Log::warning('Conversation without tenant participant', [
                'conversation_id' => $conversation->id,
                'student_id' => $studentId,
                'actor_id' => $actor?->id,
            ]);
        }
    }

    private function resolveTenantParticipantIds(Conversation $conversation, ?User $actor): array
    {
        if ($actor && !$actor->hasRole('Alumno')) {
            return [(string) $actor->id];
        }

        $candidateIds = $this->resolveExistingTenantParticipantIds($conversation);

        if ($candidateIds->isEmpty()) {
            $candidateIds = $candidateIds->merge($this->resolveConfiguredTrainerParticipantIds());
        }

        if ($candidateIds->isEmpty()) {
            $candidateIds = $candidateIds->merge($this->resolveStaffParticipantIds());
        }

        if ($candidateIds->isEmpty()) {
            $fallback = User::query()
                ->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'Alumno'))
                ->when($actor?->id, fn (Builder $q) => $q->where('id', '!=', $actor->id))
                ->orderBy('id')
                ->value('id');

            if ($fallback) {
                $candidateIds->push((string) $fallback);
            }
        }

        return $candidateIds
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function resolveConfiguredTrainerParticipantIds(): Collection
    {
        $configuredEmail = trim((string) (tenant_config('trainer_email') ?? tenant_config('contact_email') ?? ''));

        if ($configuredEmail === '') {
            return collect();
        }

        $trainerId = User::query()
            ->where('email', $configuredEmail)
            ->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'Alumno'))
            ->value('id');

        return $trainerId ? collect([(string) $trainerId]) : collect();
    }

    private function resolveStaffParticipantIds(): Collection
    {
        $rolePriority = ['Entrenador', 'trainer', 'Admin', 'admin', 'Asistente', 'assistant'];

        foreach ($rolePriority as $roleName) {
            $staffId = User::query()
                ->whereHas('roles', fn (Builder $q) => $q->where('name', $roleName))
                ->orderBy('id')
                ->value('id');

            if ($staffId) {
                return collect([(string) $staffId]);
            }
        }

        return collect();
    }

    private function resolveExistingTenantParticipantIds(Conversation $conversation): Collection
    {
        $existingIds = $conversation->participants()
            ->where('participant_type', ParticipantType::TENANT)
            ->pluck('participant_id')
            ->all();

        if (empty($existingIds)) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $existingIds)
            ->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'Alumno'))
            ->pluck('id')
            ->map(fn ($id) => (string) $id);
    }

    private function repairBrokenStudentConversationsForTenant(User $actor): void
    {
        $candidateConversations = Conversation::query()
            ->where('type', ConversationType::TENANT_STUDENT)
            ->whereNotNull('student_id')
            ->whereDoesntHave('participants', function (Builder $q) use ($actor) {
                $q->where('participant_type', ParticipantType::TENANT)
                    ->where('participant_id', (string) $actor->id);
            })
            ->with('participants')
            ->get();

        foreach ($candidateConversations as $conversation) {
            $existingTenantIds = $conversation->participants
                ->where('participant_type', ParticipantType::TENANT)
                ->pluck('participant_id')
                ->all();

            if (!empty($existingTenantIds)) {
                $hasValidStaffParticipant = User::query()
                    ->whereIn('id', $existingTenantIds)
                    ->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'Alumno'))
                    ->exists();

                if ($hasValidStaffParticipant) {
                    continue;
                }
            }

            $this->ensureConversationParticipants($conversation, (int) $conversation->student_id, $actor);
        }
    }
}
