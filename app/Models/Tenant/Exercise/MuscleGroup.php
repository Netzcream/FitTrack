<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MuscleGroup extends Model
{
    protected $table = 'exercise_muscle_groups';

    /**
     * Campos asignables.
     */
    protected $fillable = [
        'uuid',
        'code',
        'name',
        'parent_id',
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
     * Relaciones jerárquicas (self-referencing).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Relación con músculos (si existe el modelo Muscle).
     */
    public function muscles(): HasMany
    {
        return $this->hasMany(Muscle::class, 'muscle_group_id');
    }

    /**
     * Hooks: autogenera uuid, code (slug desde name) y status por defecto.
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

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
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
}
