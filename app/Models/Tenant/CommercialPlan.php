<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CommercialPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commercial_plans';

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'slug',
        'description',
        'monthly_price',
        'yearly_price',
        'currency',
        'billing_interval',
        'trial_days',
        'max_users',
        'max_teams',
        'max_projects',
        'storage_gb',
        'is_active',
        'visibility',
        'plan_type',
        'features',
        'limits',
        'external_product_id',
        'external_monthly_price_id',
        'external_yearly_price_id',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price'  => 'decimal:2',
        'trial_days'    => 'integer',
        'max_users'     => 'integer',
        'max_teams'     => 'integer',
        'max_projects'  => 'integer',
        'storage_gb'    => 'integer',
        'is_active'     => 'boolean',
        'features'      => 'array',
        'limits'        => 'array',
    ];

    // scopes de ayuda
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;
        $t = "%{$term}%";
        return $q->where(function (Builder $qq) use ($t) {
            $qq->where('name', 'like', $t)
                ->orWhere('code', 'like', $t)
                ->orWhere('slug', 'like', $t)
                ->orWhere('description', 'like', $t);
        });
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }



    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }


    // generar slug por defecto si no viene
    protected static function booted(): void
    {
        static::creating(function (CommercialPlan $plan) {
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
}
