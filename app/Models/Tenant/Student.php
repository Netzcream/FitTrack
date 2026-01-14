<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class Student extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Notifiable;

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

    public function getBirthDateAttribute()
    {
        return $this->data['birth_date'] ?? null;
    }

    public function getGenderAttribute()
    {
        return $this->data['gender'] ?? null;
    }

    public function getHeightCmAttribute()
    {
        $height = $this->data['height_cm'] ?? null;
        return $height !== null && $height !== '' ? (float) $height : null;
    }

    public function getWeightKgAttribute()
    {
        $weight = $this->data['weight_kg'] ?? null;
        return $weight !== null && $weight !== '' ? (float) $weight : null;
    }

    public function getImcAttribute()
    {
        $height = $this->height_cm;
        $weight = $this->weight_kg;

        if ($height === null || $weight === null || $height <= 0) {
            return null;
        }

        $heightM = $height / 100;
        return round($weight / ($heightM ** 2), 1);
    }

    public function getTimezoneAttribute()
    {
        return $this->data['timezone'] ?? null;
    }

    public function getCurrentLevelAttribute()
    {
        return $this->data['current_level'] ?? null;
    }

    public function getLanguageAttribute()
    {
        return $this->data['communication_data']['language'] ?? null;
    }

    public function getNotificationsAttribute()
    {
        return $this->data['notifications'] ?? [];
    }

    public function getTrainingDataAttribute()
    {
        return $this->data['training_data'] ?? [];
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

    public function planAssignments()
    {
        return $this->hasMany(StudentPlanAssignment::class);
    }

    public function currentPlanAssignment()
    {
        return $this->hasOne(StudentPlanAssignment::class)
            ->where('status', \App\Enums\PlanAssignmentStatus::ACTIVE)
            ->orderByDesc('starts_at');
    }

    public function pendingPlanAssignment()
    {
        return $this->hasOne(StudentPlanAssignment::class)
            ->where('status', \App\Enums\PlanAssignmentStatus::PENDING)
            ->orderBy('starts_at');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'student_id');
    }

    public function workouts(): HasMany
    {
        return $this->hasMany(Workout::class);
    }

    public function weightEntries(): HasMany
    {
        return $this->hasMany(StudentWeightEntry::class);
    }

    public function latestWeight(): HasOne
    {
        return $this->hasOne(StudentWeightEntry::class)
            ->latestOfMany('recorded_at');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function pendingInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date', 'asc');
    }

    public function paidInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class)
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc');
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
