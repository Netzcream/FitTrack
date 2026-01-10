<?php

namespace App\Policies\Central;

use App\Models\User;
use App\Models\Central\Conversation;
use App\Enums\ConversationType;
use App\Enums\ParticipantType;

class ConversationPolicy
{
    /**
     * Determine if the user can view the conversation
     */
    public function view(User $user, Conversation $conversation): bool
    {
        // Only Central users can access central_tenant conversations
        if ($conversation->type !== ConversationType::CENTRAL_TENANT) {
            return false;
        }

        // Check if user is a participant in this conversation
        return $conversation->participants()
            ->where('participant_type', ParticipantType::CENTRAL)
            ->where('participant_id', $user->id)
            ->exists();
    }

    /**
     * Determine if the user can send messages in the conversation
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    /**
     * Determine if the user can mark the conversation as read
     */
    public function markAsRead(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    /**
     * Determine if the user can delete the conversation
     */
    public function delete(User $user, Conversation $conversation): bool
    {
        // Only admins can delete conversations
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can mute/unmute the conversation
     */
    public function toggleMute(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    /**
     * Determine if the user can create a conversation with a tenant
     */
    public function create(User $user): bool
    {
        // All authenticated Central users can create conversations with tenants
        return true;
    }
}
