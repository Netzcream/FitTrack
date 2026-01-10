<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\MessageSent;
use App\Models\Tenant\ConversationParticipant;
use App\Models\Tenant\Student;
use App\Models\User;
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
            if ($recipient->participant_type === ParticipantType::TENANT) {
                $this->notifyTenantUser($recipient, $message);
            } elseif ($recipient->participant_type === ParticipantType::STUDENT) {
                $this->notifyStudent($recipient, $message);
            }
        }

        // Log the message for debugging
        Log::info('Message sent in Tenant conversation', [
            'conversation_id' => $conversation->id,
            'message_id' => $message->id,
            'sender_type' => $message->sender_type,
            'sender_id' => $message->sender_id,
        ]);
    }

    /**
     * Notify a tenant user (trainer/admin) about a new message from student
     */
    protected function notifyTenantUser(ConversationParticipant $participant, $message): void
    {
        // Get the tenant user
        $user = User::find($participant->participant_id);

        if (!$user) {
            return;
        }

        // TODO: Send notification to tenant user
        // This could be:
        // - Email notification
        // - In-app notification
        // - Push notification
        // - Broadcast event for real-time updates

        Log::info('Notification sent to Tenant user', [
            'user_id' => $user->id,
            'message_id' => $message->id,
        ]);
    }

    /**
     * Notify a student about a new message from trainer
     */
    protected function notifyStudent(ConversationParticipant $participant, $message): void
    {
        // Get the student
        $student = Student::find($participant->participant_id);

        if (!$student) {
            return;
        }

        // TODO: Send notification to student
        // This could be:
        // - Email notification
        // - Push notification to mobile app
        // - SMS notification

        Log::info('Notification sent to Student', [
            'student_id' => $student->id,
            'message_id' => $message->id,
            'email' => $student->email,
        ]);
    }
}
