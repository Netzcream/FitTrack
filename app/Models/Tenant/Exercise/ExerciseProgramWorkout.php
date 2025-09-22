<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExerciseProgramWorkout extends Model
{
    use HasFactory;

    protected $table = 'exercise_program_workouts';

    protected $fillable = [
        'program_id','template_workout_id',
        'week_index','day_index','name','notes','order','meta',
    ];

    protected $casts = [
        'week_index' => 'integer',
        'day_index'  => 'integer',
        'order'      => 'integer',
        'meta'       => 'array',
    ];

    public function program()
    {
        return $this->belongsTo(ExerciseProgram::class, 'program_id');
    }

    public function templateWorkout()
    {
        return $this->belongsTo(ExercisePlanTemplateWorkout::class, 'template_workout_id');
    }

    public function blocks()
    {
        return $this->hasMany(ExerciseProgramBlock::class, 'program_workout_id');
    }

    public function logs()
    {
        return $this->hasMany(ExerciseProgramLog::class, 'program_workout_id');
    }
}
