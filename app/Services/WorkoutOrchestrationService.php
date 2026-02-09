<?php

namespace App\Services;

use App\Models\Tenant\Student;
use App\Models\Tenant\StudentPlanAssignment;
use App\Models\Tenant\Workout;
use App\Models\Tenant\Exercise;
use App\Enums\PlanAssignmentStatus;
use App\Enums\WorkoutStatus;
use Carbon\Carbon;

/**
 * Orquesta el flujo completo de workouts según el plan del estudiante.
 *
 * Pasos:
 * 1. Resolver plan activo (active status y fechas)
 * 2. Determinar día objetivo basado en workouts completados
 * 3. Crear/continuar workout del día
 * 4. Actualizar datos de ejercicios durante sesión
 * 5. Completar workout con tiempo, rating, notas
 * 6. Proponer actualización de peso
 * 7. Calcular progreso (%) y detectar bonificaciones
 * 8. Generar recordatorios según preferencias
 */
class WorkoutOrchestrationService
{
    /**
     * Resolver el plan activo del estudiante
     */
    public function resolveActivePlan(Student $student): ?StudentPlanAssignment
    {
        return $student->currentPlanAssignment()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();
    }

    /**
     * Obtener el siguiente day a entrenar
     * Lógica: completed_sessions % total_days + 1
     */
    public function getNextPlanDay(StudentPlanAssignment $assignment): int
    {
        $completedWorkouts = $assignment->workouts()
            ->where('status', WorkoutStatus::COMPLETED)
            ->count();

        // Contar días únicos en el snapshot
        $totalDays = collect($assignment->exercises_snapshot ?? [])
            ->pluck('day')
            ->unique()
            ->count();

        if ($totalDays === 0) {
            return 1;
        }

        return ($completedWorkouts % $totalDays) + 1;
    }

    /**
     * Obtener ciclo actual (cada ciclo es una ronda completa del plan)
     */
    public function getCurrentCycle(StudentPlanAssignment $assignment): int
    {
        $completedWorkouts = $assignment->workouts()
            ->where('status', WorkoutStatus::COMPLETED)
            ->count();

        $totalDays = collect($assignment->exercises_snapshot ?? [])
            ->pluck('day')
            ->unique()
            ->count();

        if ($totalDays === 0) {
            return 1;
        }

        return (int) floor($completedWorkouts / $totalDays) + 1;
    }

    /**
     * Obtener o crear un workout para hoy
     */
    public function getOrCreateTodayWorkout(
        Student $student,
        StudentPlanAssignment $assignment
    ): ?Workout {
        // Verificar si existe workout "in_progress" o "pending" del día actual
        $existingWorkout = $assignment->workouts()
            ->where('student_id', $student->id)
            ->whereIn('status', [WorkoutStatus::IN_PROGRESS, WorkoutStatus::PENDING])
            ->first();

        if ($existingWorkout) {
            return $existingWorkout;
        }

        // Si no, determinar el próximo día y crear uno
        $planDay = $this->getNextPlanDay($assignment);
        $sequenceIndex = $assignment->workouts()
            ->where('status', WorkoutStatus::COMPLETED)
            ->count();
        $cycleIndex = $this->getCurrentCycle($assignment);

        // Obtener ejercicios del día del snapshot
        $exercisesForDay = collect($assignment->exercises_snapshot ?? [])
            ->filter(fn($ex) => ($ex['day'] ?? 0) === $planDay)
            ->map(function($exercise) {
                // Enriquecer con información del exercise si tenemos exercise_id
                if (isset($exercise['exercise_id'])) {
                    $fullExercise = Exercise::find($exercise['exercise_id']);
                    if ($fullExercise) {
                        // Obtener imagen si existe
                            // Galería completa de imágenes
                            $images = $fullExercise->getMedia('images')->map(function ($media) {
                                return [
                                    'url' => $media->getFullUrl(),
                                    'thumb' => $media->getFullUrl('thumb'),
                                ];
                            })->toArray();

                            $mainImage = $images[0]['url'] ?? null;

                        return array_merge($exercise, [
                            'description' => $fullExercise->description,
                            'category' => $fullExercise->category,
                            'level' => $fullExercise->level,
                            'equipment' => $fullExercise->equipment,
                                'image_url' => $mainImage,
                                'images' => $images,
                        ]);
                    }
                }
                return $exercise;
            })
            ->values()
            ->toArray();

        // Crear nuevo workout
        return Workout::create([
            'student_id' => $student->id,
            'student_plan_assignment_id' => $assignment->id,
            'plan_day' => $planDay,
            'sequence_index' => $sequenceIndex + 1,
            'cycle_index' => $cycleIndex,
            'status' => WorkoutStatus::PENDING,
            'exercises_data' => $exercisesForDay,
        ]);
    }

    /**
     * Obtener número total de días en el plan
     */
    public function getTotalPlanDays(StudentPlanAssignment $assignment): int
    {
        return collect($assignment->exercises_snapshot ?? [])
            ->pluck('day')
            ->unique()
            ->count();
    }

    /**
     * Calcular esperado de sesiones del plan
     * Expected = total_days * (weeks_in_assignment)
     */
    public function calculateExpectedSessions(StudentPlanAssignment $assignment): int
    {
        $totalDays = $this->getTotalPlanDays($assignment);
        $startDate = $assignment->starts_at;
        $endDate = $assignment->ends_at;

        if (!$startDate || !$endDate) {
            return 0;
        }

        $weeks = ceil($startDate->diffInDays($endDate) / 7);
        return $totalDays * $weeks;
    }

    /**
     * Calcular progreso del plan en porcentaje
     * Permite >100% si entrena extra dentro del período
     */
    public function calculateProgress(StudentPlanAssignment $assignment): array
    {
        $completedWorkouts = $assignment->workouts()
            ->where('status', WorkoutStatus::COMPLETED)
            ->count();

        $expectedSessions = $this->calculateExpectedSessions($assignment);
        $progressPercentage = $expectedSessions > 0
            ? ($completedWorkouts / $expectedSessions) * 100
            : 0;

        return [
            'completed_workouts' => $completedWorkouts,
            'expected_sessions' => $expectedSessions,
            'progress_percentage' => round($progressPercentage, 1),
            'is_on_track' => $progressPercentage >= 100,
            'is_bonus' => $progressPercentage > 100,
        ];
    }

    /**
     * Detectar si el ciclo se completó
     * Retorna true si completó todos los días del ciclo actual
     */
    public function isCurrentCycleComplete(StudentPlanAssignment $assignment): bool
    {
        $totalDays = $this->getTotalPlanDays($assignment);
        $currentCycle = $this->getCurrentCycle($assignment);

        $completedInCycle = $assignment->workouts()
            ->where('status', WorkoutStatus::COMPLETED)
            ->where('cycle_index', $currentCycle)
            ->distinct('plan_day')
            ->count('plan_day');

        return $completedInCycle >= $totalDays;
    }

    /**
     * Verificar si este workout es "bonus" (extra en ese ciclo)
     */
    public function isWorkoutBonus(Workout $workout): bool
    {
        $assignment = $workout->planAssignment;
        $totalDays = $this->getTotalPlanDays($assignment);

        // Contar workouts completados del mismo ciclo
        $completedInCycle = $assignment->workouts()
            ->where('status', WorkoutStatus::COMPLETED)
            ->where('cycle_index', $workout->cycle_index)
            ->count();

        // Si ya completó más workouts que días totales del plan, es bonus
        return $completedInCycle > $totalDays;
    }

    /**
     * Obtener resumen de progreso del estudiante
     */
    public function getProgressSummary(Student $student): array
    {
        $activePlan = $this->resolveActivePlan($student);

        if (!$activePlan) {
            return [
                'has_active_plan' => false,
                'message' => 'No active plan assigned',
            ];
        }

        $progress = $this->calculateProgress($activePlan);
        $isCycleComplete = $this->isCurrentCycleComplete($activePlan);
        $nextPlanDay = $this->getNextPlanDay($activePlan);

        return [
            'has_active_plan' => true,
            'plan_name' => $activePlan->name,
            'plan_starts_at' => $activePlan->starts_at,
            'plan_ends_at' => $activePlan->ends_at,
            'total_plan_days' => $this->getTotalPlanDays($activePlan),
            'current_cycle' => $this->getCurrentCycle($activePlan),
            'next_plan_day' => $nextPlanDay,
            'progress' => $progress,
            'current_cycle_complete' => $isCycleComplete,
        ];
    }

    /**
     * Obtener últimos workouts completados
     */
    public function getRecentCompletedWorkouts(Student $student, int $limit = 10)
    {
        return $student->workouts()
            ->with('planAssignment')
            ->where('status', WorkoutStatus::COMPLETED)
            ->orderByDesc('completed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener promedio de duración de workouts
     */
    public function getAverageDuration(Student $student): ?int
    {
        return $student->workouts()
            ->where('status', WorkoutStatus::COMPLETED)
            ->whereNotNull('duration_minutes')
            ->avg('duration_minutes');
    }

    /**
     * Obtener promedio de rating de workouts
     */
    public function getAverageRating(Student $student): ?float
    {
        return $student->workouts()
            ->where('status', WorkoutStatus::COMPLETED)
            ->whereNotNull('rating')
            ->avg('rating');
    }
}
