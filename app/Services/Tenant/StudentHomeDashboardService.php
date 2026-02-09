<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Invoice;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentGamificationProfile;
use App\Models\Tenant\StudentPlanAssignment;
use App\Services\Api\WorkoutDataFormatter;
use App\Services\WorkoutOrchestrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentHomeDashboardService
{
    private WorkoutOrchestrationService $orchestration;
    private WorkoutDataFormatter $workoutDataFormatter;

    public function __construct()
    {
        $this->orchestration = new WorkoutOrchestrationService();
        $this->workoutDataFormatter = new WorkoutDataFormatter();
    }

    /**
     * Obtener todos los datos del home del estudiante en una sola consulta.
     */
    public function getStudentHomeData(Student $student): array
    {
        $this->ensureGamificationProfile($student);
        $gamification = $this->formatGamificationProfile($student);

        // Resolver plan activo
        $assignment = $this->orchestration->resolveActivePlan($student);

        if (!$assignment) {
            return [
                'student' => $this->getStudentMetrics($student, $gamification),
                'gamification' => $gamification,
                'active_plan' => null,
                'today_workout' => null,
                'active_workout' => null,
                'progress_data' => [],
                'trainings_this_month' => 0,
                'goal_this_month' => 12,
                'has_pending_payment' => false,
                'no_active_plan_message' => 'No tenes un plan activo. Contacta a tu entrenador.',
                'plan_history' => [],
            ];
        }

        // Obtener workout activo (in_progress) o crear uno para hoy
        $activeWorkout = $assignment->workouts()
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        // Si no hay activo, obtener o crear para hoy
        $todayWorkout = $activeWorkout ?? $this->orchestration->getOrCreateTodayWorkout($student, $assignment);

        // Calcular progreso
        $progressData = $this->orchestration->calculateProgress($assignment);

        // Entrenamientos completados este mes
        $trainingsThisMonth = $this->resolveTrainingsThisMonth($student);

        // Meta mensual
        $goalThisMonth = data_get($student->data, 'training.monthly_goal', 12);

        // Verificar si hay invoices pendientes
        $hasPendingPayment = false;
        if (class_exists(Invoice::class)) {
            $invoiceService = new InvoiceService();
            $hasPendingPayment = !empty($invoiceService->getNextPendingForStudent($student));
        }

        // Cargar historial de planes
        $planHistory = $this->getPlanHistory($student);

        return [
            'student' => $this->getStudentMetrics($student, $gamification),
            'gamification' => $gamification,
            'active_plan' => $this->getActivePlanData($assignment),
            'today_workout' => $todayWorkout ? $this->getWorkoutData($todayWorkout) : null,
            'active_workout' => $activeWorkout ? $this->getWorkoutData($activeWorkout) : null,
            'progress_data' => $progressData,
            'trainings_this_month' => $trainingsThisMonth,
            'goal_this_month' => $goalThisMonth,
            'has_pending_payment' => $hasPendingPayment,
            'no_active_plan_message' => null,
            'plan_history' => $planHistory,
        ];
    }

    /**
     * Metricas del estudiante.
     *
     * @param array<string, int|string|bool|null> $gamification
     * @return array<string, mixed>
     */
    private function getStudentMetrics(Student $student, array $gamification): array
    {
        $displayName = trim((string) ($student->full_name ?? ''));
        if ($displayName === '') {
            $displayName = (string) ($student->email ?? '');
        }

        return [
            'id' => $student->uuid,
            'name' => $displayName,
            'full_name' => $displayName,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'phone' => $student->phone,
            'weight_kg' => $student->weight_kg,
            'height_cm' => $student->height_cm,
            'imc' => $student->imc,
            'avatar_url' => $student->getFirstMediaUrl('avatar'),
            'gamification' => $gamification,
        ];
    }

    /**
     * Datos del plan activo.
     */
    private function getActivePlanData(StudentPlanAssignment $assignment): array
    {
        $daysInPlan = $assignment->exercises_by_day->count();
        $exercisesCount = $assignment->exercises_by_day->flatten(1)->count();
        $daysRemaining = now()->diffInDays($assignment->ends_at);
        $totalDays = now()->diffInDays($assignment->starts_at);

        return [
            'uuid' => $assignment->uuid,
            'plan_name' => $assignment->plan?->name ?? $assignment->name,
            'description' => $assignment->plan?->description,
            'starts_at' => $assignment->starts_at->toIso8601String(),
            'ends_at' => $assignment->ends_at->toIso8601String(),
            'days_in_plan' => $daysInPlan,
            'exercises_count' => $exercisesCount,
            'days_elapsed' => $totalDays,
            'days_remaining' => max(0, $daysRemaining),
            'progress_percentage' => $totalDays > 0 ? round((($totalDays - $daysRemaining) / $totalDays) * 100) : 0,
            'is_current' => $assignment->is_current,
        ];
    }

    /**
     * Datos del entrenamiento.
     */
    private function getWorkoutData($workout): array
    {
        if (!$workout) {
            return [];
        }

        return $this->workoutDataFormatter->format($workout);
    }

    /**
     * Entrenamientos completados este mes.
     */
    private function resolveTrainingsThisMonth(Student $student): int
    {
        if (!Schema::hasTable('workouts')) {
            return 0;
        }

        return (int) DB::table('workouts')
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->whereYear('completed_at', now()->year)
            ->whereMonth('completed_at', now()->month)
            ->count();
    }

    /**
     * Historial de planes.
     */
    private function getPlanHistory(Student $student): array
    {
        return $student->planAssignments()
            ->with('plan')
            ->orderBy('starts_at', 'desc')
            ->get()
            ->map(function ($assignment) {
                return [
                    'uuid' => $assignment->uuid,
                    'plan_name' => $assignment->plan?->name ?? $assignment->name,
                    'starts_at' => $assignment->starts_at->toIso8601String(),
                    'ends_at' => $assignment->ends_at->toIso8601String(),
                    'status' => $assignment->status,
                    'is_current' => $assignment->is_current,
                    'exercises_count' => $assignment->exercises_by_day->flatten(1)->count(),
                    'days_count' => $assignment->exercises_by_day->count(),
                ];
            })->toArray();
    }

    private function ensureGamificationProfile(Student $student): void
    {
        $student->gamificationProfile()->firstOrCreate(
            ['student_id' => $student->id],
            [
                'total_xp' => 0,
                'current_level' => 0,
                'current_tier' => 0,
                'active_badge' => 'not_rated',
            ]
        );
    }

    /**
     * @return array<string, int|string|bool|null>
     */
    private function formatGamificationProfile(Student $student): array
    {
        $student->loadMissing('gamificationProfile');
        $profile = $student->gamificationProfile;

        if (!$profile) {
            return [
                'has_profile' => false,
                'total_xp' => 0,
                'current_level' => 0,
                'current_tier' => 0,
                'tier_name' => 'Not Rated',
                'active_badge' => 'not_rated',
                'level_progress_percent' => 0,
                'xp_for_current_level' => 0,
                'xp_for_next_level' => 100,
                'xp_inside_level' => 0,
                'xp_required_inside_level' => 100,
                'total_exercises_completed' => 0,
                'last_exercise_completed_at' => null,
            ];
        }

        $xpForCurrentLevel = StudentGamificationProfile::calculateXpRequiredForLevel((int) $profile->current_level);
        $xpForNextLevel = (int) $profile->xp_for_next_level;

        return [
            'has_profile' => true,
            'total_xp' => (int) $profile->total_xp,
            'current_level' => (int) $profile->current_level,
            'current_tier' => (int) $profile->current_tier,
            'tier_name' => $profile->tier_name,
            'active_badge' => $profile->active_badge,
            'level_progress_percent' => (int) $profile->level_progress_percent,
            'xp_for_current_level' => $xpForCurrentLevel,
            'xp_for_next_level' => $xpForNextLevel,
            'xp_inside_level' => max(0, (int) $profile->total_xp - $xpForCurrentLevel),
            'xp_required_inside_level' => max(0, $xpForNextLevel - $xpForCurrentLevel),
            'total_exercises_completed' => (int) $profile->total_exercises_completed,
            'last_exercise_completed_at' => $profile->last_exercise_completed_at?->toDateString(),
        ];
    }
}
