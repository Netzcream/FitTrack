<?php

namespace App\Notifications;

use App\Models\Tenant\Student;
use App\Models\Tenant\StudentPlanAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentWithoutPlanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Student $student,
        public StudentPlanAssignment $expiredAssignment
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
            ->subject('Acción requerida: Estudiante sin plan activo')
            ->greeting('¡Atención!')
            ->line('El estudiante **' . $this->student->full_name . '** se ha quedado sin plan de entrenamiento activo.')
            ->line('**Plan vencido:** ' . $this->expiredAssignment->name)
            ->line('**Fecha de finalización:** ' . $this->expiredAssignment->ends_at->format('d/m/Y'))
            ->line('No hay planes pendientes programados para activarse automáticamente.')
            ->action('Asignar nuevo plan', route('tenant.dashboard.students.edit', $this->student->uuid))
            ->line('Por favor, asigna un nuevo plan al estudiante lo antes posible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Estudiante sin plan activo',
            'message' => $this->student->full_name . ' no tiene un plan de entrenamiento activo',
            'student_id' => $this->student->id,
            'student_uuid' => $this->student->uuid,
            'student_name' => $this->student->full_name,
            'expired_plan_name' => $this->expiredAssignment->name,
            'expired_at' => $this->expiredAssignment->ends_at->format('Y-m-d'),
            'type' => 'student_without_plan',
            'icon' => 'alert-circle',
            'priority' => 'high',
            'action_url' => route('tenant.dashboard.students.edit', $this->student->uuid),
        ];
    }
}
