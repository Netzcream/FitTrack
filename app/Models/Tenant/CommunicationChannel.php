<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CommunicationChannel extends Model
{
    use SoftDeletes;

    protected $table = 'communication_channels';

    protected $fillable = [
        'uuid',
        'name',
        'code',
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

    // Optional: route-model binding by uuid
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
