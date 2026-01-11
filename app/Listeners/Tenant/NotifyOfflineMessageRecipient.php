<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\MessageReceivedWhileOffline;
use App\Notifications\NewMessageNotification;
use App\Models\Tenant\Student;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyOfflineMessageRecipient implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(MessageReceivedWhileOffline $event): void
    {
        $message = $event->message;

        Log::info('Enviando notificación de mensaje offline', [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'recipient_type' => $event->recipientType,
            'recipient_id' => $event->recipientId,
            'sender_type' => $message->sender_type,
            'sender_id' => $message->sender_id,
        ]);

        // Enviar notificación según el tipo de destinatario
        if ($event->recipientType === 'student') {
            $student = Student::find($event->recipientId);
            if ($student && $student->email) {
                $student->notify(new NewMessageNotification($message));
            }
        } elseif ($event->recipientType === 'tenant') {
            $user = User::find($event->recipientId);
            if ($user) {
                $user->notify(new NewMessageNotification($message));
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(MessageReceivedWhileOffline $event, \Throwable $exception): void
    {
        Log::error('Error al enviar notificación de mensaje offline', [
            'message_id' => $event->message->id,
            'recipient_type' => $event->recipientType,
            'recipient_id' => $event->recipientId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
