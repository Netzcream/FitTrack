<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ExercisePlanWorkout extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'plan_id',
        'name',
        'day_index',
        'week_index',
        'focus',
        'notes',
        'order',
    ];

    public function plan()
    {
        return $this->belongsTo(ExercisePlan::class, 'plan_id');
    }

    public function blocks()
    {
        return $this->hasMany(ExercisePlanBlock::class, 'plan_workout_id')->orderBy('order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')->useDisk('public');
    }
}
