<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class Student extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'students';

    protected $fillable = [
        'uuid',
        'user_id',
        'status',
        'email',
        'first_name',
        'last_name',
        'phone',
        'goal',
        'is_user_enabled',
        'last_login_at',
        'commercial_plan_id',
        'billing_frequency',
        'account_status',
        'data',
    ];

    protected $casts = [
        'is_user_enabled' => 'boolean',
        'last_login_at'   => 'datetime',
        'data'            => 'array',
    ];

    /* -------------------------- Accessors -------------------------- */

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    /* -------------------------- Mutators --------------------------- */

    public function setDataAttribute($value): void
    {
        $this->attributes['data'] = $value ? json_encode($value) : null;
    }

    /* -------------------------- Relaciones ------------------------- */

    public function commercialPlan()
    {
        return $this->belongsTo(CommercialPlan::class, 'commercial_plan_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /* ------------------------------ UUID boot ----------------------------- */

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

    /* ------------------------- Media Library setup ------------------------ */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
        $this->addMediaCollection('documents');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 64, 64)
            ->performOnCollections('avatar')
            ->nonQueued();
    }
}
