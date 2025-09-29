<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class ExercisePlanTemplateBlock extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'exercise_plan_template_blocks';

    protected $fillable = [
        'workout_id','type','name','notes','order','meta',
    ];

    protected $casts = [
        'order' => 'integer',
        'meta'  => 'array',
    ];

    public function workout()
    {
        return $this->belongsTo(ExercisePlanTemplateWorkout::class, 'workout_id');
    }

    public function items()
    {
        return $this->hasMany(ExercisePlanTemplateItem::class, 'block_id');
    }
}
