<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\TrainingPlanExpiredWithoutReplacement;
use App\Notifications\StudentWithoutPlanNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyPlanExpiredWithoutReplacement implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(TrainingPlanExpiredWithoutReplacement $event): void
    {
        $expiredAssignment = $event->expiredAssignment;
        $student = $expiredAssignment->student;

        Log::warning('Notificando plan vencido sin reemplazo', [
            'assignment_id' => $expiredAssignment->id,
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'plan_name' => $expiredAssignment->name,
            'ended_at' => $expiredAssignment->ends_at->format('Y-m-d'),
        ]);

        // Notificar a los usuarios del tenant (trainers/admins)
        $tenantUsers = User::query()
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'trainer']))
            ->get();

        foreach ($tenantUsers as $user) {
            $user->notify(new StudentWithoutPlanNotification($student, $expiredAssignment));
        }

        // TODO: Opcionalmente notificar al estudiante también
        // if ($student->email) {
        //     $student->notify(new PlanExpiredNotification($expiredAssignment));
        // }
    }

    /**
     * Handle a job failure.
     */
    public function failed(TrainingPlanExpiredWithoutReplacement $event, \Throwable $exception): void
    {
        Log::error('Error al enviar notificación de plan vencido sin reemplazo', [
            'assignment_id' => $event->expiredAssignment->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
