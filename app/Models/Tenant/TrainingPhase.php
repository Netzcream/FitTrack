<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TrainingPhase extends Model
{
    use SoftDeletes;

    protected $table = 'training_phases';

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::orderedUuid();
            }
        });
    }

    // Si querés usar UUID en route-model binding:
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
