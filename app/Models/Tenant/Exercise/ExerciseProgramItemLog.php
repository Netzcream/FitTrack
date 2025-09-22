<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExerciseProgramItemLog extends Model
{
    use HasFactory;

    protected $table = 'exercise_program_item_logs';

    protected $fillable = [
        'program_item_id','student_id','set_index','performed','notes','meta',
    ];

    protected $casts = [
        'set_index' => 'integer',
        'performed' => 'array',
        'meta'      => 'array',
    ];

    public function item()
    {
        return $this->belongsTo(ExerciseProgramItem::class, 'program_item_id');
    }
}
