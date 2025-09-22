<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExercisePlanTemplateItem extends Model
{
    use HasFactory;

    protected $table = 'exercise_plan_template_items';

    protected $fillable = [
        'block_id','exercise_id','display_name','snapshot_exercise',
        'order','prescription','tempo','rest_seconds','rpe','external_load',
        'notes','meta',
    ];

    protected $casts = [
        'order'             => 'integer',
        'rest_seconds'      => 'integer',
        'rpe'               => 'integer',
        'external_load'     => 'boolean',
        'prescription'      => 'array',
        'snapshot_exercise' => 'array',
        'meta'              => 'array',
    ];

    public function block()
    {
        return $this->belongsTo(ExercisePlanTemplateBlock::class, 'block_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id'); // catálogo
    }
}
