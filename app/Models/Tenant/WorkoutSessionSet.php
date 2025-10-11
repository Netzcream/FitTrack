<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkoutSessionSet extends Model
{
    use HasFactory;
    protected $fillable = [
        'workout_session_id',
        'plan_item_id',
        'set_number',
        'target_load',
        'target_reps',
        'target_rest_sec',
        'actual_load',
        'actual_reps',
        'actual_rest_sec',
        'rpe',
        'notes',
        'completed_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'completed_at' => 'datetime',
    ];

    // === Relaciones ===
    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class, 'workout_session_id');
    }

    public function planItem(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant\Exercise\ExercisePlanItem::class, 'plan_item_id');
    }
}
