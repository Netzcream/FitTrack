<?php

namespace App\Listeners\Central;

use App\Events\Central\MessageSent;
use App\Models\Central\ConversationParticipant;
use App\Enums\ParticipantType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyMessageRecipients
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $message = $event->message;
        $conversation = $message->conversation;

        // Get all participants except the sender
        $recipients = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where(function ($query) use ($message) {
                $query->where('participant_type', '!=', $message->sender_type)
                    ->orWhere('participant_id', '!=', $message->sender_id);
            })
            ->get();

        foreach ($recipients as $recipient) {
            // Skip if conversation is muted
            if ($recipient->isMuted()) {
                continue;
            }

            // Send notification based on participant type
            if ($recipient->participant_type === ParticipantType::CENTRAL) {
                $this->notifyCentralUser($recipient, $message);
            } elseif ($recipient->participant_type === ParticipantType::TENANT) {
                $this->notifyTenant($recipient, $message);
            }
        }

        // Log the message for debugging
        Log::info('Message sent in Central conversation', [
            'conversation_id' => $conversation->id,
            'message_id' => $message->id,
            'sender_type' => $message->sender_type,
            'sender_id' => $message->sender_id,
        ]);
    }

    /**
     * Notify a central user about a new message
     */
    protected function notifyCentralUser(ConversationParticipant $participant, $message): void
    {
        // TODO: Send notification to central user
        // This could be:
        // - Email notification
        // - Push notification
        // - In-app notification
        // - Broadcast event for real-time updates

        Log::info('Notification sent to Central user', [
            'user_id' => $participant->participant_id,
            'message_id' => $message->id,
        ]);
    }

    /**
     * Notify a tenant about a new message
     */
    protected function notifyTenant(ConversationParticipant $participant, $message): void
    {
        // TODO: Send notification to tenant admin/users
        // This could be:
        // - Email notification
        // - Push notification to tenant admin
        // - In-app notification

        Log::info('Notification sent to Tenant', [
            'tenant_id' => $participant->participant_id,
            'message_id' => $message->id,
        ]);
    }
}
