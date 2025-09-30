<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ExercisePlan extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'source_template_id',
        'name',
        'code',
        'status',
        'phase',
        'start_date',
        'end_date',
        'notes',
        'public_notes',
        'created_by',
        'updated_by',
        'source_template_version',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function template()
    {
        return $this->belongsTo(ExercisePlanTemplate::class, 'source_template_id');
    }

    public function workouts()
    {
        return $this->hasMany(ExercisePlanWorkout::class, 'plan_id')->orderBy('order');
    }

    public function assignments()
    {
        return $this->hasMany(ExercisePlanAssignment::class, 'plan_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')->useDisk('public');
    }
}
