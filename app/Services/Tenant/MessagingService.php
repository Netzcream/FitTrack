<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationParticipant;
use App\Models\Tenant\Message;
use App\Enums\ConversationType;
use App\Enums\ParticipantType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessagingService
{
    /**
     * Find or create a conversation between Tenant and a Student
     */
    public function findOrCreateConversation(int $studentId, ?string $subject = null): Conversation
    {
        return DB::transaction(function () use ($studentId, $subject) {
            /** @var User $user */
            $user = Auth::user();
            $userId = $user->id;

            // Check if conversation already exists
            $conversation = Conversation::where('type', ConversationType::TENANT_STUDENT)
                ->where('student_id', $studentId)
                ->first();

            if ($conversation) {
                // Ensure current user is a participant
                $participantExists = ConversationParticipant::where('conversation_id', $conversation->id)
                    ->where('participant_type', ParticipantType::TENANT)
                    ->where('participant_id', $userId)
                    ->exists();

                if (!$participantExists) {
                    ConversationParticipant::create([
                        'conversation_id' => $conversation->id,
                        'participant_type' => ParticipantType::TENANT,
                        'participant_id' => $userId,
                    ]);
                }

                return $conversation;
            }

            // Create new conversation
            $conversation = Conversation::create([
                'type' => ConversationType::TENANT_STUDENT,
                'student_id' => $studentId,
                'subject' => $subject,
            ]);

            // Add participants
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'participant_type' => ParticipantType::TENANT,
                'participant_id' => $userId,
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'participant_type' => ParticipantType::STUDENT,
                'participant_id' => $studentId,
            ]);

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
        return Conversation::where('type', ConversationType::TENANT_STUDENT)
            ->where('student_id', $studentId)
            ->first();
    }
}
