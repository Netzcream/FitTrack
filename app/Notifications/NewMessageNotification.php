<?php

namespace App\Notifications;

use App\Models\Tenant\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Message $message
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $senderName = $this->getSenderName();
        $messagePreview = $this->getMessagePreview();

        return (new MailMessage)
            ->subject('Nuevo mensaje de ' . $senderName)
            ->greeting('¡Tienes un nuevo mensaje!')
            ->line('**De:** ' . $senderName)
            ->line('**Mensaje:**')
            ->line('"' . $messagePreview . '"')
            ->action('Ver conversación', $this->getConversationUrl())
            ->line('Responde cuando tengas un momento.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nuevo mensaje',
            'message' => $this->getSenderName() . ' te ha enviado un mensaje',
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_name' => $this->getSenderName(),
            'message_preview' => $this->getMessagePreview(),
            'type' => 'new_message',
            'icon' => 'message-circle',
            'action_url' => $this->getConversationUrl(),
        ];
    }

    /**
     * Get sender name based on sender type.
     */
    private function getSenderName(): string
    {
        if ($this->message->sender_type === 'App\Models\Tenant\Student') {
            $sender = \App\Models\Tenant\Student::find($this->message->sender_id);
            return $sender ? $sender->full_name : 'Estudiante';
        }

        $sender = \App\Models\User::find($this->message->sender_id);
        return $sender ? $sender->name : 'Tu entrenador';
    }

    /**
     * Get message preview (truncated).
     */
    private function getMessagePreview(): string
    {
        return strlen($this->message->body) > 100
            ? substr($this->message->body, 0, 100) . '...'
            : $this->message->body;
    }

    /**
     * Get conversation URL.
     */
    private function getConversationUrl(): string
    {
        // Ajustar según tu sistema de rutas
        return route('tenant.messages.conversation', $this->message->conversation_id);
    }
}
