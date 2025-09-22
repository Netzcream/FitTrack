<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ExerciseLevel extends Model
{
    protected $table = 'exercise_levels';

    /**
     * Campos asignables en masa.
     */
    protected $fillable = [
        'uuid',
        'code',
        'name',
        'description',
        'order',
        'status',
        'meta',
    ];

    /**
     * Casts para columnas.
     */
    protected $casts = [
        'meta'  => 'array',
        'order' => 'integer',
    ];

    /**
     * Estados permitidos (útil para validación/lógica).
     */
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    /**
     * Relaciones
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class, 'exercise_level_id');
    }

    /**
     * Boot model: autogenera uuid y (opcional) code si no se envían.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::orderedUuid();
            }

            // Si no viene code, lo inferimos del nombre.
            if (empty($model->code) && !empty($model->name)) {
                $model->code = Str::slug($model->name);
            }

            // Valor por defecto para status si no se envía (debe coincidir con la migración)
            if (empty($model->status)) {
                $model->status = self::STATUS_DRAFT;
            }
        });
    }

    /**
     * Scopes comunes
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
     * Route model binding por uuid
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
