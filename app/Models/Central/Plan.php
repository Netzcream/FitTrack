<?php

namespace App\Models\Central;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Plan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'description',
        'price',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];


    public function tenants()
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (! $model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
