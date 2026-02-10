<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Exercise;
use App\Models\Tenant\ExerciseCompletionLog;
use App\Models\Tenant\Student;
use App\Models\Tenant\StudentGamificationProfile;
use App\Models\Tenant\Workout;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Core service for the gamification workflow.
 */
class GamificationService
{
    /**
     * Processes exercise completion and awards XP once per session.
     *
     * @throws \Exception
     */
    public function processExerciseCompletion(
        Student $student,
        Exercise $exercise,
        ?Workout $workout = null,
        ?\Carbon\Carbon $completedAt = null
    ): ?ExerciseCompletionLog {
        $completedAt = $completedAt ?? now();
        $completedDate = $completedAt->toDateString();
        $sessionInstanceId = $this->resolveSessionInstanceId($workout);

        if ($this->wasExerciseCompletedInSession($student->id, $exercise->id, $sessionInstanceId)) {
            Log::info('Exercise already awarded in the same session', [
                'student_id' => $student->id,
                'exercise_id' => $exercise->id,
                'session_instance_id' => $sessionInstanceId,
            ]);

            return null;
        }

        $exerciseLevel = $exercise->level ?? 'beginner';
        $xpEarned = ExerciseCompletionLog::getXpForExerciseLevel($exerciseLevel);

        $exerciseSnapshot = [
            'name' => $exercise->name,
            'category' => $exercise->category,
            'level' => $exerciseLevel,
            'equipment' => $exercise->equipment,
        ];

        return DB::transaction(function () use (
            $student,
            $exercise,
            $workout,
            $completedDate,
            $sessionInstanceId,
            $xpEarned,
            $exerciseLevel,
            $exerciseSnapshot
        ) {
            if ($this->wasExerciseCompletedInSession($student->id, $exercise->id, $sessionInstanceId)) {
                return null;
            }

            try {
                $log = ExerciseCompletionLog::create([
                    'student_id' => $student->id,
                    'exercise_id' => $exercise->id,
                    'workout_id' => $workout?->id,
                    'session_instance_id' => $sessionInstanceId,
                    'completed_date' => $completedDate,
                    'xp_earned' => $xpEarned,
                    'exercise_level' => $exerciseLevel,
                    'exercise_snapshot' => $exerciseSnapshot,
                ]);
            } catch (QueryException $exception) {
                if ($this->isSessionDuplicateException($exception)) {
                    return null;
                }

                throw $exception;
            }

            $profile = $this->getOrCreateProfile($student);
            $oldLevel = $profile->current_level;
            $oldTier = $profile->current_tier;

            $profile->addXp($xpEarned);
            $profile->total_exercises_completed++;
            $profile->last_exercise_completed_at = $completedDate;
            $profile->save();

            if ($profile->current_level !== $oldLevel) {
                Log::info('Level up', [
                    'student_id' => $student->id,
                    'old_level' => $oldLevel,
                    'new_level' => $profile->current_level,
                    'total_xp' => $profile->total_xp,
                ]);
            }

            if ($profile->current_tier !== $oldTier) {
                Log::info('Tier changed', [
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
     * Session-scoped idempotency check.
     */
    public function wasExerciseCompletedInSession(int $studentId, int $exerciseId, string $sessionInstanceId): bool
    {
        return ExerciseCompletionLog::wasExerciseCompletedInSession($studentId, $exerciseId, $sessionInstanceId);
    }

    /**
     * Legacy helper preserved for compatibility.
     */
    public function wasExerciseCompletedToday(
        int $studentId,
        int $exerciseId,
        ?\Carbon\Carbon $date = null
    ): bool {
        return ExerciseCompletionLog::wasExerciseCompletedToday($studentId, $exerciseId, $date);
    }

    /**
     * Gets or creates student gamification profile.
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
     * Gets student profile or null when missing.
     */
    public function getProfile(Student $student): ?StudentGamificationProfile
    {
        return StudentGamificationProfile::where('student_id', $student->id)->first();
    }

    /**
     * Returns summarized gamification stats.
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
     * Returns recent exercise completion logs.
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
     * Computes missing XP to reach target level.
     */
    public function xpToReachLevel(Student $student, int $targetLevel): int
    {
        $profile = $this->getOrCreateProfile($student);
        $requiredXp = StudentGamificationProfile::calculateXpRequiredForLevel($targetLevel);

        return max(0, $requiredXp - $profile->total_xp);
    }

    /**
     * Returns level progression table for debug/admin use.
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

    private function resolveSessionInstanceId(?Workout $workout): string
    {
        if ($workout) {
            return $workout->ensureSessionInstanceId();
        }

        return (string) Str::orderedUuid();
    }

    private function isSessionDuplicateException(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        if ($sqlState !== '23000') {
            return false;
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'session_instance_id')
            || str_contains($message, 'exercise_completion_logs_student_session_exercise_unique');
    }
}
