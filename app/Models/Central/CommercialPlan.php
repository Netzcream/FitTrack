<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Models\Tenant;

class CommercialPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commercial_plans';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'is_active',
        'pricing',
        'features',
        'limits',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'pricing'   => 'array',
        'features'  => 'array',
        'limits'    => 'array',
    ];

    /* ============================================================
     |  Scopes
     |============================================================ */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;

        $term = "%{$term}%";
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', $term)
              ->orWhere('slug', 'like', $term)
              ->orWhere('description', 'like', $term);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }

    /* ============================================================
     |  Boot & helpers
     |============================================================ */
    protected static function booted(): void
    {
        // Generar UUID y slug automáticamente
        static::creating(function (CommercialPlan $plan) {
            if (empty($plan->uuid)) {
                $plan->uuid = (string) Str::uuid();
            }

            if (empty($plan->slug) && !empty($plan->name)) {
                $base = Str::slug($plan->name);
                $slug = $base;
                $i = 2;

                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }

                $plan->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /* ===========================================================
     |  Relaciones
     |============================================================ */
    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
}
