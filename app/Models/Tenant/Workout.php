<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use App\Enums\WorkoutStatus;

class Workout extends Model
{
    use HasUuids;

    protected $table = 'workouts';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
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

    public function getRouteKeyName(): string
    {
        return 'uuid';
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
        $this->update([
            'status' => WorkoutStatus::IN_PROGRESS,
            'started_at' => now(),
        ]);

        return $this;
    }

    /**
     * Completar workout
     */
    public function completeWorkout(
        int $durationMinutes,
        int $rating = null,
        string $notes = null,
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
    public function skip(string $reason = null): self
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
            return [
                'id' => $exercise['id'] ?? null,
                'name' => $exercise['name'] ?? 'Unknown',
                'completed' => $exercise['completed'] ?? false,
                'series' => $exercise['series'] ?? 0,
                'completed_series' => collect($exercise['sets'] ?? [])->filter(function ($set) {
                    return $set['completed'] ?? false;
                })->count(),
            ];
        })->toArray();
    }
}
