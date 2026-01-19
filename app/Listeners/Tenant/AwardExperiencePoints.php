<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\ExerciseCompleted;
use App\Services\Tenant\GamificationService;
use Illuminate\Support\Facades\Log;

/**
 * Listener que procesa el otorgamiento de XP cuando se completa un ejercicio
 * NOTA: Se ejecuta síncronamente para garantizar feedback inmediato al usuario
 */
class AwardExperiencePoints
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected GamificationService $gamificationService
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(ExerciseCompleted $event): void
    {
        try {
            $this->gamificationService->processExerciseCompletion(
                student: $event->student,
                exercise: $event->exercise,
                workout: $event->workout,
                completedAt: $event->completedAt
            );
        } catch (\Exception $e) {
            Log::error('Error al procesar XP por ejercicio completado', [
                'student_id' => $event->student->id,
                'exercise_id' => $event->exercise->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // No relanzamos la excepción para no bloquear otros listeners
            // El sistema de gamificación es complementario y no crítico
        }
    }
}
