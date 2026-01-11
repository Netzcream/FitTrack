<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\StudentCreated;
use App\Notifications\WelcomeStudentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendStudentWelcomeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(StudentCreated $event): void
    {
        $student = $event->student;

        Log::info('Enviando notificación de bienvenida a estudiante', [
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'student_email' => $student->email,
            'created_by' => $event->createdBy,
        ]);

        // Enviar notificación de bienvenida al estudiante
        if ($student->email) {
            $student->notify(new WelcomeStudentNotification($student));
        }

        // TODO: Opcionalmente notificar al trainer que lo creó
        // if ($event->createdBy) {
        //     $trainer = User::find($event->createdBy);
        //     $trainer?->notify(new StudentCreatedNotification($student));
        // }
    }

    /**
     * Handle a job failure.
     */
    public function failed(StudentCreated $event, \Throwable $exception): void
    {
        Log::error('Error al enviar notificación de estudiante creado', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
