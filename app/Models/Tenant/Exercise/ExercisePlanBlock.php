<?php

namespace App\Models\Tenant\Exercise;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Enums\Exercise\BlockType;

class ExercisePlanBlock extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'plan_workout_id',
        'name',
        'type',
        'is_circuit',
        'rounds',
        'notes',
        'order',
    ];

    protected $casts = [
        'type' => BlockType::class,
    ];

    public function workout()
    {
        return $this->belongsTo(ExercisePlanWorkout::class, 'plan_workout_id');
    }

    public function items()
    {
        return $this->hasMany(ExercisePlanItem::class, 'plan_block_id')->orderBy('order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')->useDisk('public');
    }
}
