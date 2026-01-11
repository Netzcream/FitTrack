<?php

namespace App\Notifications;

use App\Models\Tenant\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeStudentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Student $student
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
        return (new MailMessage)
            ->subject('¡Bienvenido a FitTrack!')
            ->greeting('¡Hola ' . $this->student->first_name . '!')
            ->line('Tu cuenta ha sido creada exitosamente en FitTrack.')
            ->line('Ahora puedes acceder a tu panel de estudiante donde podrás:')
            ->line('✓ Ver tus planes de entrenamiento')
            ->line('✓ Registrar tu progreso')
            ->line('✓ Comunicarte con tu entrenador')
            ->line('✓ Hacer seguimiento de tus objetivos')
            ->action('Acceder a mi cuenta', url('/login'))
            ->line('Si tienes alguna duda, no dudes en contactar con tu entrenador.')
            ->salutation('¡Bienvenido al equipo!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Cuenta creada exitosamente',
            'message' => 'Tu cuenta de FitTrack ha sido creada. ¡Bienvenido!',
            'student_id' => $this->student->id,
            'student_name' => $this->student->full_name,
            'type' => 'student_created',
            'icon' => 'user-plus',
            'action_url' => route('tenant.dashboard'),
        ];
    }
}
