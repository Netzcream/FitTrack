<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExercisePlanTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'exercise_plan_templates';

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'status',
        'version',
        'description',
        'is_public',
        'meta',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'version'   => 'integer',
        'meta'      => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            $m->uuid ??= (string) Str::orderedUuid();
            $m->version ??= 1;
        });
    }

    public function workouts()
    {
        return $this->hasMany(ExercisePlanTemplateWorkout::class, 'template_id');
    }
}
