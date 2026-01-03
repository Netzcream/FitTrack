<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class WorkoutExercise extends Model
{
    protected $fillable = [
        'workout_id',
        'exercise_id',
        'sets_completed',
        'reps_per_set',
        'weight_used_kg',
        'duration_seconds',
        'rest_time_seconds',
        'notes',
        'completed_at',
        'meta',
    ];

    protected $casts = [
        'sets_completed'    => 'integer',
        'reps_per_set'      => 'array',
        'weight_used_kg'    => 'float',
        'duration_seconds'  => 'integer',
        'rest_time_seconds' => 'integer',
        'completed_at'      => 'datetime',
        'meta'              => 'array',
    ];

    /* ---------------- Relationships ---------------- */

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
