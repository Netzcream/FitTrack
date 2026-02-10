<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $connection = 'tenant';

    protected $table = 'devices';

    protected $fillable = [
        'user_id',
        'tenant_id',
        'platform',
        'expo_push_token',
        'last_seen_at',
        'is_active',
        'deactivated_at',
        'deactivation_reason',
        'last_error_code',
        'last_error_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
