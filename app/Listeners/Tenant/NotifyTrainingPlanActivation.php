<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\TrainingPlanActivated;
use App\Notifications\TrainingPlanActivatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyTrainingPlanActivation implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(TrainingPlanActivated $event): void
    {
        $assignment = $event->assignment;
        $student = $assignment->student;

        Log::info('Notificando activación de plan de entrenamiento', [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'plan_name' => $assignment->name,
            'activation_type' => $event->activationType,
            'starts_at' => $assignment->starts_at->format('Y-m-d'),
            'ends_at' => $assignment->ends_at->format('Y-m-d'),
        ]);

        // Notificar al estudiante sobre el plan activado
        if ($student->email) {
            $student->notify(new TrainingPlanActivatedNotification($assignment, $event->activationType));
        }

        // TODO: Si fue automático, notificar también al trainer
        // if ($event->activationType === 'automatic') {
        //     $student->trainer?->notify(new StudentPlanAutoActivatedNotification($student, $assignment));
        // }
    }

    /**
     * Handle a job failure.
     */
    public function failed(TrainingPlanActivated $event, \Throwable $exception): void
    {
        Log::error('Error al enviar notificación de plan activado', [
            'assignment_id' => $event->assignment->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
