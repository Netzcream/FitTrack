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
        'status',
        'email',
        'first_name',
        'last_name',
        'phone',
        'timezone',
        'goal',
        'is_user_enabled',
        'last_login_at',
        'current_level',
        'commercial_plan_id',
        'billing_frequency',
        'account_status',
        'personal_data',
        'health_data',
        'training_data',
        'communication_data',
        'extra_data',
    ];

    protected $casts = [
        'is_user_enabled'     => 'boolean',
        'last_login_at'       => 'datetime',
        'personal_data'       => 'array',
        'health_data'         => 'array',
        'training_data'       => 'array',
        'communication_data'  => 'array',
        'extra_data'          => 'array',
    ];

    /* -------------------------- Accessors virtuales -------------------------- */

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function getBirthDateAttribute(): ?string
    {
        return $this->personal_data['birth_date'] ?? null;
    }

    public function getGenderAttribute(): ?string
    {
        return $this->personal_data['gender'] ?? null;
    }

    public function getHeightCmAttribute(): ?float
    {
        return isset($this->personal_data['height_cm'])
            ? (float) $this->personal_data['height_cm']
            : null;
    }

    public function getWeightKgAttribute(): ?float
    {
        return isset($this->personal_data['weight_kg'])
            ? (float) $this->personal_data['weight_kg']
            : null;
    }

    public function getImcAttribute(): ?float
    {
        $weight = $this->weight_kg;
        $height = $this->height_cm;

        if ($weight && $height) {
            $m = $height / 100;
            return round($weight / ($m * $m), 2);
        }

        return null;
    }

    public function getLanguageAttribute(): ?string
    {
        return $this->communication_data['language'] ?? null;
    }

    public function getNotificationsAttribute(): array
    {
        return $this->communication_data['notifications'] ?? [];
    }

    public function getEmergencyContactAttribute(): ?array
    {
        return $this->extra_data['emergency_contact'] ?? null;
    }

    /* -------------------------- Mutators automáticos -------------------------- */

    public function setBirthDateAttribute($value): void
    {
        $data = $this->personal_data ?? [];
        $data['birth_date'] = $value;
        $this->personal_data = $data;
    }

    public function setGenderAttribute($value): void
    {
        $data = $this->personal_data ?? [];
        $data['gender'] = $value;
        $this->personal_data = $data;
    }

    public function setHeightCmAttribute($value): void
    {
        $data = $this->personal_data ?? [];
        $data['height_cm'] = $value;
        $this->personal_data = $data;
    }

    public function setWeightKgAttribute($value): void
    {
        $data = $this->personal_data ?? [];
        $data['weight_kg'] = $value;
        $this->personal_data = $data;
    }

    public function setLanguageAttribute($value): void
    {
        $data = $this->communication_data ?? [];
        $data['language'] = $value;
        $this->communication_data = $data;
    }

    public function setNotificationsAttribute($value): void
    {
        $data = $this->communication_data ?? [];
        $data['notifications'] = $value;
        $this->communication_data = $data;
    }

    public function setEmergencyContactAttribute($value): void
    {
        $data = $this->extra_data ?? [];
        $data['emergency_contact'] = $value;
        $this->extra_data = $data;
    }

    /* ----------------------------- Relaciones ----------------------------- */

    public function commercialPlan()
    {
        return $this->belongsTo(CommercialPlan::class, 'commercial_plan_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'email', 'email');
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
