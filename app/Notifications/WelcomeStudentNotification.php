<?php

namespace App\Notifications;

use App\Models\Tenant\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeStudentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Student $student,
        public string $registrationUrl
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Completa tu registro en FitTrack')
            ->greeting('Hola ' . $this->student->first_name . '!')
            ->line('Tu entrenador creo tu cuenta en FitTrack.')
            ->line('Para activar tu acceso, primero debes definir tu clave.')
            ->action('Definir mi clave', $this->registrationUrl)
            ->line('Luego podras ingresar y ver tus planes de entrenamiento, progreso y mensajes.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Cuenta creada',
            'message' => 'Tu cuenta fue creada y se envio un email para completar el registro.',
            'student_id' => $this->student->id,
            'student_name' => $this->student->full_name,
            'type' => 'student_created',
            'icon' => 'user-plus',
        ];
    }
}
