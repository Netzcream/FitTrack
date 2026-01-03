<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Workout extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'student_id',
        'training_plan_id',
        'date',
        'duration_minutes',
        'status',
        'notes',
        'rating',
        'meta',
    ];

    protected $casts = [
        'date'             => 'date',
        'duration_minutes' => 'integer',
        'rating'           => 'integer',
        'meta'             => 'array',
    ];

    /* ---------------- Relationships ---------------- */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function trainingPlan()
    {
        return $this->belongsTo(TrainingPlan::class);
    }

    public function exercises()
    {
        return $this->hasMany(WorkoutExercise::class);
    }

    /* ---------------- Boot ---------------- */

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            // Valores por defecto
            if (empty($model->status)) {
                $model->status = 'completed';
            }
        });
    }

    /* ---------------- Accessors ---------------- */

    public function getExercisesCompletedAttribute(): int
    {
        return $this->exercises()->count();
    }
}
