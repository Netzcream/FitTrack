<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Student;
use App\Models\Tenant\Workout;
use App\Models\Tenant\StudentWeightEntry;
use Carbon\Carbon;

class ProgressDashboardService
{
    /**
     * Obtener todos los datos del dashboard de progreso en una sola consulta
     */
    public static function getDashboardData(Student $student): array
    {
        return [
            'student' => self::getStudentMetrics($student),
            'workouts' => self::getWorkoutMetrics($student),
            'weight' => self::getWeightMetrics($student),
            'progress' => self::getProgressMetrics($student),
            'recent_workouts' => self::getRecentWorkouts($student),
        ];
    }

    /**
     * Métricas del estudiante (peso, altura, IMC)
     */
    private static function getStudentMetrics(Student $student): array
    {
        return [
            'current_weight_kg' => $student->weight_kg,
            'height_cm' => $student->height_cm,
            'imc' => $student->imc,
        ];
    }

    /**
     * Métricas de entrenamientos (completados, días entrenados, adherencia)
     */
    private static function getWorkoutMetrics(Student $student): array
    {
        // Entrenamientos completados
        $completedCount = Workout::where('student_id', $student->id)
            ->where('status', 'completed')
            ->count();

        // Días únicos con entrenamientos completados
        $uniqueDays = Workout::where('student_id', $student->id)
            ->where('status', 'completed')
            ->selectRaw('DATE(completed_at) as workout_date')
            ->distinct()
            ->count();

        // Cálculo de adherencia
        $adherence = 0;
        $currentAssignment = $student->currentPlanAssignment;
        if ($currentAssignment && $currentAssignment->starts_at && $currentAssignment->ends_at) {
            $planDays = $currentAssignment->starts_at->diffInDays($currentAssignment->ends_at);
            $adherence = $planDays > 0 ? round(($uniqueDays / $planDays) * 100, 2) : 0;
        }

        // Calcular métricas manualmente desde los workouts completados
        $completedWorkouts = Workout::where('student_id', $student->id)
            ->where('status', 'completed')
            ->get();

        $totalDuration = 0;
        $totalCalories = 0;
        $count = 0;

        foreach ($completedWorkouts as $workout) {
            if (is_array($workout->exercises_data)) {
                $totalDuration += $workout->exercises_data['duration_minutes'] ?? 0;
                $totalCalories += $workout->exercises_data['calories_burned'] ?? 0;
                $count++;
            }
        }

        $avgDuration = $count > 0 ? round($totalDuration / $count) : 0;
        $avgCalories = $count > 0 ? round($totalCalories / $count) : 0;

        return [
            'workouts_completed' => $completedCount,
            'days_trained' => $uniqueDays,
            'adherence_percentage' => $adherence,
            'average_duration_minutes' => $avgDuration,
            'average_calories' => $avgCalories,
        ];
    }

    /**
     * Métricas de peso (peso actual, cambio, evolución)
     */
    private static function getWeightMetrics(Student $student): array
    {
        $latest = StudentWeightEntry::where('student_id', $student->id)
            ->orderBy('recorded_at', 'desc')
            ->first();

        $sevenDaysAgo = StudentWeightEntry::where('student_id', $student->id)
            ->where('recorded_at', '>=', now()->subDays(7))
            ->orderBy('recorded_at', 'asc')
            ->first();

        $thirtyDaysAgo = StudentWeightEntry::where('student_id', $student->id)
            ->where('recorded_at', '>=', now()->subDays(30))
            ->orderBy('recorded_at', 'asc')
            ->first();

        $weightChange7Days = null;
        if ($sevenDaysAgo && $latest) {
            $weightChange7Days = round($latest->weight_kg - $sevenDaysAgo->weight_kg, 2);
        }

        $weightChange30Days = null;
        if ($thirtyDaysAgo && $latest) {
            $weightChange30Days = round($latest->weight_kg - $thirtyDaysAgo->weight_kg, 2);
        }

        // Historial de peso (últimos 30 días)
        $history = StudentWeightEntry::where('student_id', $student->id)
            ->where('recorded_at', '>=', now()->subDays(30))
            ->orderBy('recorded_at', 'asc')
            ->get()
            ->map(function ($entry) {
                return [
                    'date' => $entry->recorded_at->format('Y-m-d'),
                    'weight_kg' => (float) $entry->weight_kg,
                    'recorded_at' => $entry->recorded_at->toIso8601String(),
                ];
            })
            ->values();

        return [
            'current_weight_kg' => $latest ? (float) $latest->weight_kg : null,
            'change_7_days_kg' => $weightChange7Days,
            'change_30_days_kg' => $weightChange30Days,
            'weight_history' => $history,
        ];
    }

    /**
     * Progreso mensual (últimos 6 meses)
     */
    private static function getProgressMetrics(Student $student): array
    {
        $sixMonthsAgo = now()->subMonths(6);

        $monthlyProgress = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->clone()->startOfMonth();
            $monthEnd = $month->clone()->endOfMonth();

            $workoutsInMonth = Workout::where('student_id', $student->id)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$monthStart, $monthEnd])
                ->count();

            $monthlyProgress[] = [
                'month' => $month->format('Y-m'),
                'month_name' => $month->format('M Y'),
                'workouts_completed' => $workoutsInMonth,
            ];
        }

        return [
            'monthly' => $monthlyProgress,
        ];
    }

    /**
     * Entrenamientos recientes (últimos 10)
     */
    private static function getRecentWorkouts(Student $student): array
    {
        $workouts = Workout::where('student_id', $student->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($workout) {
                $exercisesData = $workout->exercises_data ?? [];

                $duration = $exercisesData['duration_minutes'] ?? 0;
                $calories = $exercisesData['calories_burned'] ?? 0;
                $exercises = $exercisesData['exercises'] ?? [];
                $exerciseCount = is_array($exercises) ? count($exercises) : 0;

                return [
                    'id' => $workout->id,
                    'uuid' => $workout->uuid,
                    'date' => $workout->completed_at->format('Y-m-d'),
                    'completed_at' => $workout->completed_at->toIso8601String(),
                    'plan_day' => $workout->plan_day,
                    'duration_minutes' => $duration,
                    'calories_burned' => $calories,
                    'exercises_count' => $exerciseCount,
                ];
            })
            ->values();

        return $workouts->toArray();
    }
}
