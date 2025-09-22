<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Equipment extends Model
{
    protected $table = 'exercise_equipment';

    /**
     * Campos asignables.
     */
    protected $fillable = [
        'uuid',
        'code',
        'name',
        'is_machine',
        'description',
        'order',
        'status',
        'meta',
    ];

    /**
     * Casts.
     */
    protected $casts = [
        'is_machine' => 'bool',
        'order'      => 'integer',
        'meta'       => 'array',
    ];

    /**
     * Estados permitidos.
     */
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    /**
     * Relaciones.
     */
    public function exercises(): BelongsToMany
    {
        // Pivot: exercise_equipment_exercise (equipment_id, exercise_id, is_required, timestamps)
        return $this->belongsToMany(Exercise::class, 'exercise_equipment_exercise', 'equipment_id', 'exercise_id')
            ->withPivot(['is_required'])
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

    public function scopeMachines($query)
    {
        return $query->where('is_machine', true);
    }

    public function scopeFreeImplements($query)
    {
        return $query->where('is_machine', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }
}
