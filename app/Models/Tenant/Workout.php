<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\WorkoutStatus;
use App\Traits\HasUuid;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Workout extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static array $legacyUuidOldColumnCache = [];

    protected $table = 'workouts';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'uuid',
        'session_instance_id',
        'student_id',
        'student_plan_assignment_id',
        'plan_day',
        'sequence_index',
        'cycle_index',
        'started_at',
        'completed_at',
        'duration_minutes',
        'status',
        'rating',
        'notes',
        'exercises_data',
        'meta',
    ];

    protected $casts = [
        'exercises_data' => 'array',
        'meta' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'rating' => 'integer',
        'plan_day' => 'integer',
        'sequence_index' => 'integer',
        'cycle_index' => 'integer',
        'duration_minutes' => 'integer',
        'status' => WorkoutStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $workout): void {
            if (!self::hasLegacyUuidOldColumn($workout)) {
                return;
            }

            $uuid = $workout->uuid ?: (string) Str::orderedUuid();

            $workout->uuid = $uuid;
            $workout->setAttribute('uuid_old', $uuid);
        });
    }

    protected static function hasLegacyUuidOldColumn(self $workout): bool
    {
        $connection = $workout->getConnection();
        $cacheKey = implode(':', [
            $connection->getName(),
            $connection->getDatabaseName(),
            $workout->getTable(),
        ]);

        if (!array_key_exists($cacheKey, self::$legacyUuidOldColumnCache)) {
            self::$legacyUuidOldColumnCache[$cacheKey] = Schema::connection($connection->getName())
                ->hasColumn($workout->getTable(), 'uuid_old');
        }

        return self::$legacyUuidOldColumnCache[$cacheKey];
    }



    /**
     * Relación: pertenece a un estudiante
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relación: pertenece a una asignación de plan
     */
    public function planAssignment(): BelongsTo
    {
        return $this->belongsTo(StudentPlanAssignment::class, 'student_plan_assignment_id');
    }



    /* ======================== Accessors ======================== */

    /**
     * Verificar si el workout está completado
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === WorkoutStatus::COMPLETED && $this->completed_at !== null;
    }

    /**
     * Verificar si el workout está en progreso
     */
    public function getIsInProgressAttribute(): bool
    {
        return $this->status === WorkoutStatus::IN_PROGRESS && $this->started_at !== null;
    }

    /**
     * Obtener duración formateada
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration_minutes) {
            return null;
        }
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }

    /**
     * Obtener la descripción del día del plan
     */
    public function getPlanDayLabelAttribute(): string
    {
        return "Day {$this->plan_day}";
    }

    /**
     * Obtener ejercicios del día actual
     */
    public function getExercisesAttribute(): array
    {
        return $this->exercises_data ?? [];
    }

    /* ======================== Methods ======================== */

    /**
     * Marcar workout como iniciado
     */
    public function startWorkout(): self
    {
        $sessionInstanceId = $this->session_instance_id;
        if (!is_string($sessionInstanceId) || trim($sessionInstanceId) === '') {
            $sessionInstanceId = (string) Str::orderedUuid();
        }

        $this->update([
            'status' => WorkoutStatus::IN_PROGRESS,
            'started_at' => now(),
            'session_instance_id' => $sessionInstanceId,
        ]);

        return $this;
    }

    /**
     * Garantiza un session_instance_id persistido para usar como run_id de sesión.
     */
    public function ensureSessionInstanceId(): string
    {
        if (is_string($this->session_instance_id) && trim($this->session_instance_id) !== '') {
            return $this->session_instance_id;
        }

        $sessionInstanceId = (string) Str::orderedUuid();

        $this->forceFill([
            'session_instance_id' => $sessionInstanceId,
        ])->save();

        return $sessionInstanceId;
    }

    /**
     * Completar workout
     */
    public function completeWorkout(
        int $durationMinutes,
        ?int $rating = null,
        ?string $notes = null,
        array $survey = []
    ): self {
        $this->update([
            'status' => WorkoutStatus::COMPLETED,
            'completed_at' => now(),
            'duration_minutes' => $durationMinutes,
            'rating' => $rating,
            'notes' => $notes,
            'meta' => array_merge($this->meta ?? [], [
                'survey' => $survey,
                'completed_at_iso' => now()->toIso8601String(),
            ]),
        ]);

        return $this;
    }

    /**
     * Actualizar datos de ejercicios durante el entrenamiento
     */
    public function updateExercisesData(array $exercisesData): self
    {
        $this->exercises_data = $exercisesData;
        $this->save();

        return $this;
    }

    /**
     * Saltar este workout
     */
    public function skip(?string $reason = null): self
    {
        $this->update([
            'status' => WorkoutStatus::SKIPPED,
            'meta' => array_merge($this->meta ?? [], [
                'skip_reason' => $reason,
                'skipped_at' => now()->toIso8601String(),
            ]),
        ]);

        return $this;
    }

    /**
     * Obtener el progreso actual de cada ejercicio
     */
    public function getExerciseProgress(): array
    {
        if (empty($this->exercises_data)) {
            return [];
        }

        return collect($this->exercises_data)->map(function ($exercise) {
            $sets = is_array($exercise['sets'] ?? null) ? $exercise['sets'] : [];
            $setsTotal = count($sets);
            $completedSets = collect($sets)->filter(function ($set) {
                return is_array($set) && (bool) ($set['completed'] ?? false);
            })->count();
            $setsCompletionPercentage = $setsTotal > 0
                ? round(($completedSets / $setsTotal) * 100, 1)
                : 0.0;

            return [
                'id' => $exercise['id'] ?? ($exercise['exercise_id'] ?? null),
                'exercise_id' => $exercise['exercise_id'] ?? ($exercise['id'] ?? null),
                'name' => $exercise['name'] ?? 'Unknown',
                'completed' => (bool) ($exercise['completed'] ?? false),
                // Backward-compatible aliases.
                'series' => $setsTotal,
                'completed_series' => $completedSets,
                'sets_total' => $setsTotal,
                'sets_completed' => $completedSets,
                'sets_remaining' => max(0, $setsTotal - $completedSets),
                'sets_completion_percentage' => $setsCompletionPercentage,
            ];
        })->toArray();
    }
}
