<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Student;
use App\Models\Tenant\Exercise;
use App\Models\Tenant\Workout;
use App\Models\Tenant\StudentGamificationProfile;
use App\Models\Tenant\ExerciseCompletionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio central del sistema de gamificación
 *
 * Responsabilidades:
 * - Procesar completado de ejercicios
 * - Otorgar XP con validación anti-farming
 * - Actualizar niveles y tiers automáticamente
 * - Gestionar perfiles de gamificación
 */
class GamificationService
{
    /**
     * Procesa el completado de un ejercicio y otorga XP si corresponde
     *
     * @throws \Exception Si hay error en la validación o persistencia
     */
    public function processExerciseCompletion(
        Student $student,
        Exercise $exercise,
        ?Workout $workout = null,
        ?\Carbon\Carbon $completedAt = null
    ): ?ExerciseCompletionLog {
        $completedAt = $completedAt ?? now();
        $completedDate = $completedAt->toDateString();

        // Validación anti-farming: verificar si ya fue completado hoy
        if ($this->wasExerciseCompletedToday($student->id, $exercise->id, $completedAt)) {
            Log::info('Ejercicio ya completado hoy (anti-farming)', [
                'student_id' => $student->id,
                'exercise_id' => $exercise->id,
                'date' => $completedDate,
            ]);

            return null; // No otorgar XP
        }

        // Obtener el nivel del ejercicio
        $exerciseLevel = $exercise->level ?? 'beginner';
        $xpEarned = ExerciseCompletionLog::getXpForExerciseLevel($exerciseLevel);

        // Crear snapshot del ejercicio para auditoría
        $exerciseSnapshot = [
            'name' => $exercise->name,
            'category' => $exercise->category,
            'level' => $exerciseLevel,
            'equipment' => $exercise->equipment,
        ];

        // Transacción para garantizar consistencia
        return DB::transaction(function () use (
            $student,
            $exercise,
            $workout,
            $completedDate,
            $xpEarned,
            $exerciseLevel,
            $exerciseSnapshot
        ) {
            // 1. Registrar el log de completado (con unique constraint en DB)
            $log = ExerciseCompletionLog::create([
                'student_id' => $student->id,
                'exercise_id' => $exercise->id,
                'workout_id' => $workout?->id,
                'completed_date' => $completedDate,
                'xp_earned' => $xpEarned,
                'exercise_level' => $exerciseLevel,
                'exercise_snapshot' => $exerciseSnapshot,
            ]);

            // 2. Obtener o crear el perfil de gamificación
            $profile = $this->getOrCreateProfile($student);

            // 3. Otorgar XP y recalcular nivel/tier
            $oldLevel = $profile->current_level;
            $oldTier = $profile->current_tier;

            $profile->addXp($xpEarned);
            $profile->total_exercises_completed++;
            $profile->last_exercise_completed_at = $completedDate;
            $profile->save();

            // 4. Log de progreso si hubo cambio de nivel/tier
            if ($profile->current_level !== $oldLevel) {
                Log::info('¡Nivel up!', [
                    'student_id' => $student->id,
                    'old_level' => $oldLevel,
                    'new_level' => $profile->current_level,
                    'total_xp' => $profile->total_xp,
                ]);
            }

            if ($profile->current_tier !== $oldTier) {
                Log::info('¡Cambio de tier!', [
                    'student_id' => $student->id,
                    'old_tier' => $oldTier,
                    'new_tier' => $profile->current_tier,
                    'badge' => $profile->active_badge,
                ]);
            }

            return $log;
        });
    }

    /**
     * Verifica si un ejercicio ya fue completado por un alumno en una fecha específica
     */
    public function wasExerciseCompletedToday(
        int $studentId,
        int $exerciseId,
        ?\Carbon\Carbon $date = null
    ): bool {
        return ExerciseCompletionLog::wasExerciseCompletedToday($studentId, $exerciseId, $date);
    }

    /**
     * Obtiene o crea el perfil de gamificación de un alumno
     */
    public function getOrCreateProfile(Student $student): StudentGamificationProfile
    {
        return StudentGamificationProfile::firstOrCreate(
            ['student_id' => $student->id],
            [
                'total_xp' => 0,
                'current_level' => 0,
                'current_tier' => 0,
                'active_badge' => 'not_rated',
                'total_exercises_completed' => 0,
                'meta' => [],
            ]
        );
    }

    /**
     * Obtiene el perfil de gamificación de un alumno (o null si no existe)
     */
    public function getProfile(Student $student): ?StudentGamificationProfile
    {
        return StudentGamificationProfile::where('student_id', $student->id)->first();
    }

    /**
     * Obtiene estadísticas de gamificación de un alumno
     */
    public function getStudentStats(Student $student): array
    {
        $profile = $this->getProfile($student);

        if (!$profile) {
            return [
                'has_profile' => false,
                'total_xp' => 0,
                'current_level' => 0,
                'current_tier' => 0,
                'tier_name' => 'Not Rated',
                'active_badge' => 'not_rated',
                'total_exercises' => 0,
                'level_progress' => 0,
                'xp_for_next_level' => 100,
            ];
        }

        return [
            'has_profile' => true,
            'total_xp' => $profile->total_xp,
            'current_level' => $profile->current_level,
            'current_tier' => $profile->current_tier,
            'tier_name' => $profile->tier_name,
            'active_badge' => $profile->active_badge,
            'total_exercises' => $profile->total_exercises_completed,
            'level_progress' => $profile->level_progress_percent,
            'xp_for_next_level' => $profile->xp_for_next_level,
            'last_completed' => $profile->last_exercise_completed_at?->format('Y-m-d'),
        ];
    }

    /**
     * Obtiene el historial reciente de ejercicios completados
     */
    public function getRecentCompletions(Student $student, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return ExerciseCompletionLog::where('student_id', $student->id)
            ->with('exercise')
            ->orderByDesc('completed_date')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Calcula cuánto XP necesita un alumno para alcanzar un nivel específico
     */
    public function xpToReachLevel(Student $student, int $targetLevel): int
    {
        $profile = $this->getOrCreateProfile($student);
        $requiredXp = StudentGamificationProfile::calculateXpRequiredForLevel($targetLevel);

        return max(0, $requiredXp - $profile->total_xp);
    }

    /**
     * Obtiene la tabla de niveles y XP requerido (útil para debugging/admin)
     */
    public function getLevelTable(int $maxLevel = 30): array
    {
        $table = [];

        for ($level = 0; $level <= $maxLevel; $level++) {
            $xpRequired = StudentGamificationProfile::calculateXpRequiredForLevel($level);
            $tier = StudentGamificationProfile::calculateTierFromLevel($level);
            $badge = StudentGamificationProfile::getBadgeNameForTier($tier);

            $table[] = [
                'level' => $level,
                'xp_required' => $xpRequired,
                'tier' => $tier,
                'badge' => $badge,
            ];
        }

        return $table;
    }
}
