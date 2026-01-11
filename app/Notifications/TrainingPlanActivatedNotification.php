<?php

namespace App\Notifications;

use App\Models\Tenant\StudentPlanAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingPlanActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public StudentPlanAssignment $assignment,
        public string $activationType
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
        $wasAutomatic = $this->activationType === 'automatic';

        $mail = (new MailMessage)
            ->subject($wasAutomatic ? 'Tu nuevo plan de entrenamiento ya está activo' : 'Plan de entrenamiento asignado')
            ->greeting('¡Hola ' . $this->assignment->student->first_name . '!');

        if ($wasAutomatic) {
            $mail->line('Tu nuevo plan de entrenamiento **"' . $this->assignment->name . '"** ha sido activado automáticamente.');
        } else {
            $mail->line('Se te ha asignado un nuevo plan de entrenamiento: **"' . $this->assignment->name . '"**');
        }

        return $mail
            ->line('**Fecha de inicio:** ' . $this->assignment->starts_at->format('d/m/Y'))
            ->line('**Fecha de finalización:** ' . $this->assignment->ends_at->format('d/m/Y'))
            ->line('**Duración:** ' . $this->assignment->starts_at->diffInDays($this->assignment->ends_at) . ' días')
            ->action('Ver mi plan', route('tenant.student.plans.show', $this->assignment->uuid))
            ->line('¡Es hora de alcanzar tus objetivos! 💪');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->activationType === 'automatic' ? 'Plan activado automáticamente' : 'Nuevo plan asignado',
            'message' => 'El plan "' . $this->assignment->name . '" está ahora activo',
            'assignment_id' => $this->assignment->id,
            'assignment_uuid' => $this->assignment->uuid,
            'plan_name' => $this->assignment->name,
            'starts_at' => $this->assignment->starts_at->format('Y-m-d'),
            'ends_at' => $this->assignment->ends_at->format('Y-m-d'),
            'activation_type' => $this->activationType,
            'type' => 'plan_activated',
            'icon' => 'calendar-check',
            'action_url' => route('tenant.student.plans.show', $this->assignment->uuid),
        ];
    }
}
