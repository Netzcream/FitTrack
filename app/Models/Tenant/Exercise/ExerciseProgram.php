<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ExerciseProgram extends Model
{
    use HasFactory;

    protected $table = 'exercise_programs';

    protected $fillable = [
        'uuid','name','status',
        'template_id','template_version','origin_info',
        'start_date','notes','meta',
    ];

    protected $casts = [
        'template_version' => 'integer',
        'origin_info'      => 'array',
        'start_date'       => 'date',
        'meta'             => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            $m->uuid ??= (string) Str::orderedUuid();
        });
    }

    public function template()
    {
        return $this->belongsTo(ExercisePlanTemplate::class, 'template_id');
    }

    public function workouts()
    {
        return $this->hasMany(ExerciseProgramWorkout::class, 'program_id');
    }

    public function assignments()
    {
        return $this->hasMany(ExerciseProgramAssignment::class, 'program_id');
    }
}
