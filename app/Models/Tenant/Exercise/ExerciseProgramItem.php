<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExerciseProgramItem extends Model
{
    use HasFactory;

    protected $table = 'exercise_program_items';

    protected $fillable = [
        'program_block_id','template_item_id','exercise_id',
        'display_name','snapshot_exercise','order','prescription',
        'tempo','rest_seconds','rpe','external_load','notes','meta',
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
        return $this->belongsTo(ExerciseProgramBlock::class, 'program_block_id');
    }

    public function templateItem()
    {
        return $this->belongsTo(ExercisePlanTemplateItem::class, 'template_item_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id'); // catálogo
    }

    public function logs()
    {
        return $this->hasMany(ExerciseProgramItemLog::class, 'program_item_id');
    }
}
