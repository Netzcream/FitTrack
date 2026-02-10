<?php

namespace App\Services\Tenant;

use App\Enums\ConversationType;
use App\Enums\ParticipantType;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationParticipant;
use App\Models\Tenant\Message;
use Illuminate\Support\Facades\DB;

class MessagingService
{
    public const TENANT_PARTICIPANT_ID = 0;

    /**
     * Find or create a conversation between Tenant and a Student
     */
    public function findOrCreateConversation(int $studentId, ?string $subject = null): Conversation
    {
        return DB::transaction(function () use ($studentId, $subject) {
            $conversation = Conversation::where('type', ConversationType::TENANT_STUDENT)
                ->where('student_id', $studentId)
                ->first();

            if (! $conversation) {
                $conversation = Conversation::create([
                    'type' => ConversationType::TENANT_STUDENT,
                    'student_id' => $studentId,
                    'subject' => $subject,
                ]);
            }

            $this->ensureStudentParticipant($conversation->id, $studentId);
            $this->ensureTenantParticipant($conversation->id);

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
                $this->ensureStudentParticipant($conversation->id, (int) $conversation->student_id);
                $this->ensureTenantParticipant($conversation->id);
            }

            $message = $conversation->addMessage(
                $senderType->value,
                $senderId,
                $body,
                $attachments
            );

            // Mark as read for sender (their own messages don't count as unread)
            if ($senderType === ParticipantType::TENANT) {
                $this->markAsRead($conversationId, $senderType, self::TENANT_PARTICIPANT_ID);
            } else {
                $this->markAsRead($conversationId, $senderType, $senderId);
            }

            // Fire event for realtime + push notifications
            event(new \App\Events\Tenant\MessageCreated(
                messageId: (int) $message->id,
                tenantId: tenancy()->initialized ? (string) tenancy()->tenant?->id : null
            ));

            return $message;
        });
    }

    /**
     * Mark conversation as read for a participant
     */
    public function markAsRead(int $conversationId, ParticipantType $participantType, string|int $participantId): void
    {
        $participantId = $this->normalizeParticipantId($participantType, $participantId);

        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('participant_type', $participantType)
            ->where('participant_id', (string) $participantId)
            ->update(['last_read_at' => now()]);
    }

    /**
     * Get conversations for a participant
     */
    public function getConversations(
        ParticipantType $participantType,
        string|int $participantId,
        int $perPage = 15
    ) {
        $participantId = $this->normalizeParticipantId($participantType, $participantId);

        if ($participantType === ParticipantType::TENANT) {
            $this->backfillTenantParticipantForStudentConversations();
        }

        $conversationIds = ConversationParticipant::where('participant_type', $participantType)
            ->where('participant_id', (string) $participantId)
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
    public function getUnreadCount(ParticipantType $participantType, string|int $participantId): int
    {
        $participantId = $this->normalizeParticipantId($participantType, $participantId);

        if ($participantType === ParticipantType::TENANT) {
            $this->backfillTenantParticipantForStudentConversations();
        }

        $conversationIds = ConversationParticipant::where('participant_type', $participantType)
            ->where('participant_id', (string) $participantId)
            ->pluck('conversation_id');

        return Message::whereIn('conversation_id', $conversationIds)
            ->whereRaw('(messages.created_at > COALESCE((
                SELECT last_read_at
                FROM conversation_participants
                WHERE conversation_id = messages.conversation_id
                AND participant_type = ?
                AND participant_id = ?
            ), "1970-01-01 00:00:00"))', [$participantType->value, (string) $participantId])
            ->count();
    }

    /**
     * Mute/unmute a conversation
     */
    public function toggleMute(
        int $conversationId,
        ParticipantType $participantType,
        string|int $participantId,
        bool $mute
    ): void {
        $participantId = $this->normalizeParticipantId($participantType, $participantId);

        $participant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('participant_type', $participantType)
            ->where('participant_id', (string) $participantId)
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

        if (! $conversation) {
            return null;
        }

        $this->ensureStudentParticipant($conversation->id, $studentId);
        $this->ensureTenantParticipant($conversation->id);

        return $conversation;
    }

    private function ensureStudentParticipant(int $conversationId, int $studentId): void
    {
        ConversationParticipant::firstOrCreate([
            'conversation_id' => $conversationId,
            'participant_type' => ParticipantType::STUDENT,
            'participant_id' => (string) $studentId,
        ]);
    }

    private function ensureTenantParticipant(int $conversationId): void
    {
        ConversationParticipant::firstOrCreate([
            'conversation_id' => $conversationId,
            'participant_type' => ParticipantType::TENANT,
            'participant_id' => (string) self::TENANT_PARTICIPANT_ID,
        ]);
    }

    private function normalizeParticipantId(ParticipantType $participantType, string|int $participantId): string|int
    {
        if ($participantType === ParticipantType::TENANT) {
            return self::TENANT_PARTICIPANT_ID;
        }

        return $participantId;
    }

    private function backfillTenantParticipantForStudentConversations(): void
    {
        DB::connection('tenant')->statement(
            'INSERT IGNORE INTO conversation_participants (conversation_id, participant_type, participant_id, last_read_at, muted_at)
             SELECT c.id, ?, ?, NULL, NULL
             FROM conversations c
             LEFT JOIN conversation_participants cp
               ON cp.conversation_id = c.id
              AND cp.participant_type = ?
              AND cp.participant_id = ?
             WHERE c.type = ?
               AND cp.id IS NULL',
            [
                ParticipantType::TENANT->value,
                (string) self::TENANT_PARTICIPANT_ID,
                ParticipantType::TENANT->value,
                (string) self::TENANT_PARTICIPANT_ID,
                ConversationType::TENANT_STUDENT->value,
            ]
        );
    }
}
