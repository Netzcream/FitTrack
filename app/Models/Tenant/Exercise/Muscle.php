<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Muscle extends Model
{
    protected $table = 'exercise_muscles';

    /**
     * Campos asignables.
     */
    protected $fillable = [
        'uuid',
        'code',
        'muscle_group_id',
        'name',
        'description',
        'order',
        'status',
        'meta',
    ];

    /**
     * Casts.
     */
    protected $casts = [
        'meta'  => 'array',
        'order' => 'integer',
    ];

    /**
     * Estados permitidos.
     */
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    /**
     * (Sugerido) Roles de participación en el pivot.
     */
    public const ROLE_PRIMARY   = 'primary';
    public const ROLE_SECONDARY = 'secondary';
    public const ROLE_STABILIZER = 'stabilizer';

    /**
     * Relaciones.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(MuscleGroup::class, 'muscle_group_id');
    }

    public function exercises(): BelongsToMany
    {
        // Asegurate que exista la tabla pivot `exercise_exercise_muscle`
        // con columnas: exercise_id, muscle_id, role (string), involvement_pct (integer/smallint)
        return $this->belongsToMany(Exercise::class, 'exercise_exercise_muscle', 'muscle_id', 'exercise_id')
            ->withPivot(['role', 'involvement_pct'])
            ->withTimestamps();
    }

    /**
     * Hooks: autogenera uuid, code (slug) y status por defecto.
     */
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
        });
    }

    /**
     * Scopes comunes.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Route model binding por uuid.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Helpers para el pivot (cómodos al trabajar con ejercicios).
     * Usar al cargar relación con ->with('exercises') o ->load('exercises').
     */
    public function primaryExercises()
    {
        return $this->exercises()->wherePivot('role', self::ROLE_PRIMARY);
    }

    public function secondaryExercises()
    {
        return $this->exercises()->wherePivot('role', self::ROLE_SECONDARY);
    }

    public function stabilizerExercises()
    {
        return $this->exercises()->wherePivot('role', self::ROLE_STABILIZER);
    }
}
