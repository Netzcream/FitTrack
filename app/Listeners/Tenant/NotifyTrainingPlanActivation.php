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
        $initializedByListener = $this->initializeTenantIfNeeded($event->tenantId);

        try {
            $assignment->loadMissing('student');
            $student = $assignment->student;

            if (! $student) {
                Log::warning('No se notifico plan asignado: assignment sin estudiante asociado', [
                    'assignment_id' => $assignment->id,
                    'tenant_id' => $event->tenantId,
                ]);

                return;
            }

            Log::info('Notificando activacion/asignacion de plan de entrenamiento', [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
                'student_name' => $student->full_name,
                'plan_name' => $assignment->name,
                'activation_type' => $event->activationType,
                'tenant_id' => $event->tenantId,
                'starts_at' => $assignment->starts_at?->format('Y-m-d'),
                'ends_at' => $assignment->ends_at?->format('Y-m-d'),
                'new_plan_notifications_enabled' => $student->shouldReceiveNewPlanNotification(),
            ]);

            if (! $student->shouldReceiveNewPlanNotification()) {
                return;
            }

            if ($student->email) {
                $student->notify(new TrainingPlanActivatedNotification($assignment, $event->activationType));
            }
        } finally {
            if ($initializedByListener) {
                tenancy()->end();
            }
        }

        // TODO: Si fue automatico, notificar tambien al trainer
        // if ($event->activationType === 'automatic') {
        //     $student->trainer?->notify(new StudentPlanAutoActivatedNotification($student, $assignment));
        // }
    }

    private function initializeTenantIfNeeded(?string $tenantId): bool
    {
        if (! is_string($tenantId) || trim($tenantId) === '') {
            return false;
        }

        try {
            $currentTenantId = tenancy()->initialized ? (string) tenancy()->tenant?->id : null;
            if ($currentTenantId === $tenantId) {
                return false;
            }

            tenancy()->initialize($tenantId);

            return true;
        } catch (\Throwable $exception) {
            Log::warning('No se pudo inicializar tenancy en listener de plan activado', [
                'tenant_id' => $tenantId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(TrainingPlanActivated $event, \Throwable $exception): void
    {
        Log::error('Error al enviar notificacion de plan activado', [
            'assignment_id' => $event->assignment->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
