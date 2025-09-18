<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $table = 'payment_methods';

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'description',
        'instructions',
        'config',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config'    => 'array', // json <-> array
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::orderedUuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
