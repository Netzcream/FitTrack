<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class ExercisePlanTemplateWorkout extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'exercise_plan_template_workouts';

    protected $fillable = [
        'template_id','week_index','day_index','name','notes','order','meta',
    ];

    protected $casts = [
        'week_index' => 'integer',
        'day_index'  => 'integer',
        'order'      => 'integer',
        'meta'       => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(ExercisePlanTemplate::class, 'template_id');
    }

    public function blocks()
    {
        return $this->hasMany(ExercisePlanTemplateBlock::class, 'workout_id');
    }
}
