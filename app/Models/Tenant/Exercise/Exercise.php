<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;

class Exercise extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'exercise_exercises';

    // Enums cerrados (coherentes con la migración)
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    public const MOD_REPS       = 'reps';
    public const MOD_TIME       = 'time';
    public const MOD_DISTANCE   = 'distance';
    public const MOD_CALORIES   = 'calories';
    public const MOD_RPE        = 'rpe';
    public const MOD_LOAD_ONLY  = 'load_only';
    public const MOD_TEMPO_ONLY = 'tempo_only';

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'status',
        'exercise_level_id',
        'movement_pattern_id',
        'exercise_plane_id',
        'unilateral',
        'external_load',
        'default_modality',
        'default_prescription',
        'tempo_notation',
        'range_of_motion_notes',
        'equipment_notes',
        'setup_steps',
        'execution_cues',
        'common_mistakes',
        'breathing',
        'safety_notes',
        'meta',
    ];

    protected $casts = [
        'unilateral'           => 'bool',
        'external_load'        => 'bool',
        'default_prescription' => 'array',
        'setup_steps'          => 'array',
        'execution_cues'       => 'array',
        'common_mistakes'      => 'array',
        'meta'                 => 'array',
    ];

    /* ---------------- Relaciones ---------------- */
    public function level(): BelongsTo
    {
        return $this->belongsTo(ExerciseLevel::class, 'exercise_level_id');
    }

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(MovementPattern::class, 'movement_pattern_id');
    }

    public function plane(): BelongsTo
    {
        return $this->belongsTo(ExercisePlane::class, 'exercise_plane_id');
    }

    public function muscles(): BelongsToMany
    {
        return $this->belongsToMany(Muscle::class, 'exercise_exercise_muscle', 'exercise_id', 'muscle_id')
            ->withPivot(['role', 'involvement_pct'])
            ->withTimestamps();
    }

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'exercise_equipment_exercise', 'exercise_id', 'equipment_id')
            ->withPivot(['is_required'])
            ->withTimestamps();
    }

    /* ---------------- Scopes útiles ---------------- */
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeStatus(Builder $q, string $status): Builder
    {
        return $q->where('status', $status);
    }

    public function scopeWithModality(Builder $q, string $modality): Builder
    {
        return $q->where('default_modality', $modality);
    }

    public function scopeByLevel(Builder $q, int $levelId): Builder
    {
        return $q->where('exercise_level_id', $levelId);
    }

    public function scopeByPattern(Builder $q, int $patternId): Builder
    {
        return $q->where('movement_pattern_id', $patternId);
    }

    public function scopeByPlane(Builder $q, int $planeId): Builder
    {
        return $q->where('exercise_plane_id', $planeId);
    }

    public function scopeUnilateral(Builder $q, bool $value = true): Builder
    {
        return $q->where('unilateral', $value);
    }

    public function scopeExternalLoad(Builder $q, bool $value = true): Builder
    {
        return $q->where('external_load', $value);
    }

    /* --------------- Spatie Media v11 --------------- */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
        $this->addMediaCollection('videos');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(\Spatie\Image\Enums\Fit::Crop, 64, 64)
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->width(1000)
            ->performOnCollections('images')
            ->nonQueued();
    }

    /* ---------------- Hooks ---------------- */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::orderedUuid();
            }
            if (empty($model->code) && !empty($model->name)) {
                $model->code = Str::slug($model->name);
            }
            if (empty($model->status)) {
                $model->status = self::STATUS_DRAFT;
            }
            if (empty($model->default_modality)) {
                $model->default_modality = self::MOD_REPS; // coincide con default de la migración
            }
        });
    }

    /* ---------------- Routing ---------------- */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
