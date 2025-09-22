<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExerciseProgramLog extends Model
{
    use HasFactory;

    protected $table = 'exercise_program_logs';

    protected $fillable = [
        'program_workout_id','student_id','performed_at',
        'duration_minutes','rpe_session','notes','meta',
    ];

    protected $casts = [
        'performed_at'     => 'datetime',
        'duration_minutes' => 'integer',
        'rpe_session'      => 'integer',
        'meta'             => 'array',
    ];

    public function workout()
    {
        return $this->belongsTo(ExerciseProgramWorkout::class, 'program_workout_id');
    }
}
