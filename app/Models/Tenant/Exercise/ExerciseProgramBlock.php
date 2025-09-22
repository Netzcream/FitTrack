<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExerciseProgramBlock extends Model
{
    use HasFactory;

    protected $table = 'exercise_program_blocks';

    protected $fillable = [
        'program_workout_id','template_block_id',
        'type','name','notes','order','meta',
    ];

    protected $casts = [
        'order' => 'integer',
        'meta'  => 'array',
    ];

    public function workout()
    {
        return $this->belongsTo(ExerciseProgramWorkout::class, 'program_workout_id');
    }

    public function templateBlock()
    {
        return $this->belongsTo(ExercisePlanTemplateBlock::class, 'template_block_id');
    }

    public function items()
    {
        return $this->hasMany(ExerciseProgramItem::class, 'program_block_id');
    }
}
