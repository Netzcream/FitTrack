<?php
namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Exercise extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'category',
        'level',
        'equipment',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta'      => 'array',
    ];

    /* ---------------- Relationships ---------------- */
    public function plans()
    {
        return $this->belongsToMany(TrainingPlan::class, 'plan_exercise')
                    ->withPivot(['day', 'detail', 'notes', 'meta'])
                    ->withTimestamps();
    }

    /* ---------------- Scopes ---------------- */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;
        $t = "%{$term}%";
        return $q->where(function ($qq) use ($t) {
            $qq->where('name', 'like', $t)
               ->orWhere('category', 'like', $t)
               ->orWhere('equipment', 'like', $t);
        });
    }

    /* ---------------- Boot ---------------- */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /* ---------------- Media Library ---------------- */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(300)->height(300)->fit('crop', 300, 300);
    }

    /* ---------------- Helpers ---------------- */
    public function __toString(): string
    {
        return $this->name ?? static::class;
    }
}
