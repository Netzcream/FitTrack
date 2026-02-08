<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\StudentCreated;
use App\Notifications\WelcomeStudentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class SendStudentWelcomeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(StudentCreated $event): void
    {
        $student = $event->student;
        $registrationUrl = $event->registrationUrl;

        Log::info('Enviando notificacion de bienvenida a estudiante', [
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'student_email' => $student->email,
            'created_by' => $event->createdBy,
        ]);

        if (! $registrationUrl) {
            $user = $student->user;

            if (! $user || ! $user->email) {
                Log::warning('No se pudo enviar onboarding de alumno: falta user/email asociado', [
                    'student_id' => $student->id,
                    'student_email' => $student->email,
                ]);

                return;
            }

            $token = Password::broker()->createToken($user);
            $registrationUrl = route('tenant.password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);
        }

        if ($student->email) {
            $student->notify(new WelcomeStudentNotification($student, $registrationUrl));
        }

        // TODO: Opcionalmente notificar al trainer que lo creo
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
        Log::error('Error al enviar notificacion de estudiante creado', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
