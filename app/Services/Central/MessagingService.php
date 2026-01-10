<?php

namespace App\Services\Central;

use App\Models\Central\Conversation;
use App\Models\Central\ConversationParticipant;
use App\Models\Central\Message;
use App\Enums\ConversationType;
use App\Enums\ParticipantType;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class MessagingService
{
    /**
     * Find or create a conversation between Central and a Tenant
     */
    public function findOrCreateConversation(string $tenantId, ?string $subject = null): Conversation
    {
        // Check if conversation already exists
        $conversation = Conversation::where('type', ConversationType::CENTRAL_TENANT)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($conversation) {
            // Ensure both participants exist for existing conversations
            ConversationParticipant::firstOrCreate([
                'conversation_id' => $conversation->id,
                'participant_type' => ParticipantType::CENTRAL,
                'participant_id' => 1,
            ]);

            ConversationParticipant::firstOrCreate([
                'conversation_id' => $conversation->id,
                'participant_type' => ParticipantType::TENANT,
                'participant_id' => $tenantId,
            ]);

            return $conversation->fresh(['participants']);
        }

        // Create new conversation
        $conversation = Conversation::create([
            'type' => ConversationType::CENTRAL_TENANT,
            'tenant_id' => $tenantId,
            'subject' => $subject,
        ]);

        // Add participants
        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'participant_type' => ParticipantType::CENTRAL,
            'participant_id' => 1, // Central has a single virtual ID
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'participant_type' => ParticipantType::TENANT,
            'participant_id' => $tenantId,
        ]);

        return $conversation->fresh(['participants']);
    }

    /**
     * Send a message in a conversation
     */
    public function sendMessage(
        int $conversationId,
        ParticipantType $senderType,
        string|int $senderId,
        string $body,
        ?array $attachments = null
    ): Message {
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
        event(new \App\Events\Central\MessageSent($message));

        return $message;
    }

    /**
     * Mark conversation as read for a participant
     */
    public function markAsRead(int $conversationId, ParticipantType $participantType, string|int $participantId): void
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
        string|int $participantId,
        int $perPage = 15
    ) {
        $conversationIds = ConversationParticipant::where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->pluck('conversation_id');

        return Conversation::whereIn('id', $conversationIds)
            ->with(['lastMessage', 'participants'])
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
        $conversationIds = ConversationParticipant::where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->pluck('conversation_id');

        // Compare message time against participant's last_read_at using COALESCE,
        // avoiding references to a non-existent messages.last_read_at column.
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
    public function toggleMute(int $conversationId, ParticipantType $participantType, string|int $participantId, bool $mute): void
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
}
