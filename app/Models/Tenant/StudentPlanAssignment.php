<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Support\Str;
use App\Models\Tenant\Exercise;

class StudentPlanAssignment extends Model
{
    protected $table = 'student_plan_assignments';

    protected $fillable = [
        'uuid',
        'student_id',
        'training_plan_id',
        'name',
        'meta',
        'exercises_snapshot',
        'is_active',
        'starts_at',
        'ends_at',
        'overrides',
    ];

    protected $casts = [
        'meta' => 'array',
        'exercises_snapshot' => 'array',
        'overrides' => 'array',
        'is_active' => 'boolean',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::orderedUuid();
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TrainingPlan::class, 'training_plan_id');
    }

    public function getVersionLabelAttribute(): string
    {
        $version = $this->meta['version'] ?? null;
        if (!$version) {
            return '';
        }
        $formatted = number_format((float) $version, 1, '.', '');
        return 'v' . $formatted;
    }

    public function getExercisesByDayAttribute()
    {
        $snapshot = collect($this->exercises_snapshot ?? []);

        if ($snapshot->isEmpty()) {
            return collect([]);
        }

        // Enrich with exercise names if snapshot lacks them
        $needsLookup = $snapshot->contains(function ($ex) {
            return empty($ex['name']) && !empty($ex['exercise_id']);
        });

        if ($needsLookup) {
            $exerciseMap = Exercise::whereIn('id', $snapshot->pluck('exercise_id')->filter()->unique())
                ->get(['id', 'name'])
                ->keyBy('id');

            $snapshot = $snapshot->map(function ($item) use ($exerciseMap) {
                if (empty($item['name']) && !empty($item['exercise_id']) && $exerciseMap->has($item['exercise_id'])) {
                    $item['name'] = $exerciseMap[$item['exercise_id']]->name;
                }
                return $item;
            });
        }

        return $snapshot->groupBy(fn ($ex) => $ex['day'] ?? 0)->sortKeys();
    }

    public function getIsCurrentAttribute(): bool
    {
        $today = now();
        return $this->is_active
            && (!$this->starts_at || $this->starts_at->lte($today))
            && (!$this->ends_at || $this->ends_at->isFuture());
    }

    // Backward-compat: alias used in existing blades
    public function getAssignedFromAttribute()
    {
        return $this->starts_at;
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
