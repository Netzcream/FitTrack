<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExerciseProgramAssignment extends Model
{
    use HasFactory;

    protected $table = 'exercise_program_assignments';

    protected $fillable = [
        'program_id','assignable_type','assignable_id',
        'coach_id','starts_at','ends_at','status','meta',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
        'meta'      => 'array',
    ];

    public function program()
    {
        return $this->belongsTo(ExerciseProgram::class, 'program_id');
    }

    public function assignable()
    {
        return $this->morphTo();
    }

    public function coach()
    {
        // Ajustá a tu clase real de usuario del tenant si no es ésta
        return $this->belongsTo(\App\Models\User::class, 'coach_id');
    }
}
