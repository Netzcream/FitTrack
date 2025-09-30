<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ExercisePlanItem extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'plan_block_id',
        'exercise_id',
        'order',
        'sets',
        'reps',
        'reps_min',
        'reps_max',
        'rest_sec',
        'tempo',
        'rir',
        'load_prescription',
        'notes',
    ];

    protected $casts = [
        'load_prescription' => 'array',
    ];

    public function block()
    {
        return $this->belongsTo(ExercisePlanBlock::class, 'plan_block_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')->useDisk('public');
    }
}
