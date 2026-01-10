<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasMessaging
{
    /**
     * Get all conversations for this participant
     */
    public function conversations()
    {
        return $this->hasMany($this->getConversationParticipantModel())
            ->with('conversation.lastMessage');
    }

    /**
     * Get unread conversations count
     */
    public function unreadConversationsCount(): int
    {
        return $this->conversations()
            ->whereHas('conversation.messages', function ($query) {
                $query->where('created_at', '>', function ($subQuery) {
                    $subQuery->select('last_read_at')
                        ->from('conversation_participants')
                        ->whereColumn('conversation_id', 'messages.conversation_id')
                        ->where('participant_type', $this->getParticipantType())
                        ->where('participant_id', $this->id);
                })->orWhereNull('last_read_at');
            })
            ->count();
    }

    /**
     * Get unread messages count across all conversations
     */
    public function unreadMessagesCount(): int
    {
        $conversationIds = $this->conversations()->pluck('conversation_id');

        return $this->getMessageModel()::whereIn('conversation_id', $conversationIds)
            ->where('created_at', '>', function ($query) {
                $query->select('last_read_at')
                    ->from('conversation_participants')
                    ->whereColumn('conversation_id', 'messages.conversation_id')
                    ->where('participant_type', $this->getParticipantType())
                    ->where('participant_id', $this->id);
            })
            ->orWhereNull('last_read_at')
            ->count();
    }

    /**
     * Mark conversation as read for this participant
     */
    public function markConversationAsRead(int $conversationId): void
    {
        $this->getConversationParticipantModel()::where('conversation_id', $conversationId)
            ->where('participant_type', $this->getParticipantType())
            ->where('participant_id', $this->id)
            ->update(['last_read_at' => now()]);
    }

    /**
     * Get the participant type for this model
     */
    abstract protected function getParticipantType(): string;

    /**
     * Get the conversation participant model class
     */
    abstract protected function getConversationParticipantModel(): string;

    /**
     * Get the message model class
     */
    abstract protected function getMessageModel(): string;
}
