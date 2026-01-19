<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ExerciseCompletionLog extends Model
{
    protected $table = 'exercise_completion_logs';

    protected $fillable = [
        'uuid',
        'student_id',
        'exercise_id',
        'workout_id',
        'completed_date',
        'xp_earned',
        'exercise_level',
        'exercise_snapshot',
    ];

    protected $casts = [
        'completed_date' => 'date',
        'xp_earned' => 'integer',
        'exercise_snapshot' => 'array',
    ];

    /* -------------------------- Relationships -------------------------- */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    /* -------------------------- Boot -------------------------- */

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /* -------------------------- Static Helpers -------------------------- */

    /**
     * Verifica si un ejercicio ya fue completado por un alumno en una fecha específica
     */
    public static function wasExerciseCompletedToday(int $studentId, int $exerciseId, ?\Carbon\Carbon $date = null): bool
    {
        $date = $date ?? now();

        return self::where('student_id', $studentId)
            ->where('exercise_id', $exerciseId)
            ->where('completed_date', $date->toDateString())
            ->exists();
    }

    /**
     * Obtiene el XP correspondiente a un nivel de ejercicio
     */
    public static function getXpForExerciseLevel(string $level): int
    {
        return match (strtolower($level)) {
            'beginner', 'principiante' => 10,
            'intermediate', 'intermedio' => 15,
            'advanced', 'avanzado' => 20,
            default => 10, // Por defecto, si no se especifica
        };
    }
}
