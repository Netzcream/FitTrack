<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Student;
use App\Models\Tenant\StudentPlanAssignment;
use App\Models\Tenant\Invoice;
use App\Services\WorkoutOrchestrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentHomeDashboardService
{
    private WorkoutOrchestrationService $orchestration;

    public function __construct()
    {
        $this->orchestration = new WorkoutOrchestrationService();
    }

    /**
     * Obtener todos los datos del home del estudiante en una sola consulta
     */
    public function getStudentHomeData(Student $student): array
    {
        // Resolver plan activo
        $assignment = $this->orchestration->resolveActivePlan($student);

        if (!$assignment) {
            return [
                'student' => $this->getStudentMetrics($student),
                'active_plan' => null,
                'today_workout' => null,
                'active_workout' => null,
                'progress_data' => [],
                'trainings_this_month' => 0,
                'goal_this_month' => 12,
                'has_pending_payment' => false,
                'no_active_plan_message' => 'No tenés un plan activo. Contactá a tu entrenador.',
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
            'student' => $this->getStudentMetrics($student),
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
     * Métricas del estudiante
     */
    private function getStudentMetrics(Student $student): array
    {
        return [
            'id' => $student->uuid,
            'name' => $student->name,
            'email' => $student->email,
            'phone' => $student->phone,
            'weight_kg' => $student->weight_kg,
            'height_cm' => $student->height_cm,
            'imc' => $student->imc,
            'avatar_url' => $student->getFirstMediaUrl('avatar'),
        ];
    }

    /**
     * Datos del plan activo
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
     * Datos del entrenamiento
     */
    private function getWorkoutData($workout): array
    {
        if (!$workout) {
            return [];
        }

        return [
            'uuid' => $workout->uuid,
            'date' => $workout->created_at->toDateString(),
            'status' => $workout->status,
            'is_in_progress' => $workout->is_in_progress,
            'completed_at' => $workout->completed_at?->toIso8601String(),
            'duration_minutes' => $workout->exercises_data['duration_minutes'] ?? 0,
            'calories_burned' => $workout->exercises_data['calories_burned'] ?? 0,
            'exercises_data' => $workout->exercises_data,
        ];
    }

    /**
     * Entrenamientos completados este mes
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
     * Historial de planes
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
}
