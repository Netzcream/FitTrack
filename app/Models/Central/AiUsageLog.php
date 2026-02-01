<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use App\Models\Tenant;

class AiUsageLog extends Model
{
    use CentralConnection;

    protected $table = 'ai_usage_logs';

    protected $fillable = [
        'tenant_id',
        'month',
        'usage_count',
        'limit',
        'plan_slug',
        'meta',
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'limit' => 'integer',
        'meta' => 'array',
    ];

    /* ============================================================
     |  Relationships
     |============================================================ */

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /* ============================================================
     |  Scopes
     |============================================================ */

    /**
     * Scope para filtrar por tenant.
     */
    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope para filtrar por mes.
     */
    public function scopeForMonth($query, string $month)
    {
        return $query->where('month', $month);
    }

    /**
     * Scope para obtener registros recientes (últimos N meses).
     */
    public function scopeRecent($query, int $months = 6)
    {
        $startMonth = now()->subMonths($months)->format('Y-m');
        return $query->where('month', '>=', $startMonth)
            ->orderBy('month', 'desc');
    }

    /* ============================================================
     |  Helpers estáticos
     |============================================================ */

    /**
     * Registra o actualiza el uso del mes actual para un tenant.
     */
    public static function recordUsage(string $tenantId, string $month, int $usageCount, int $limit, ?string $planSlug = null): self
    {
        return static::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'month' => $month,
            ],
            [
                'usage_count' => $usageCount,
                'limit' => $limit,
                'plan_slug' => $planSlug,
            ]
        );
    }

    /**
     * Obtiene el historial completo de un tenant.
     */
    public static function getHistory(string $tenantId, int $months = 12): \Illuminate\Support\Collection
    {
        return static::forTenant($tenantId)
            ->recent($months)
            ->get();
    }

    /**
     * Obtiene estadísticas agregadas de un tenant.
     */
    public static function getStats(string $tenantId): array
    {
        $logs = static::forTenant($tenantId)->recent(12)->get();

        if ($logs->isEmpty()) {
            return [
                'total_usage' => 0,
                'avg_usage' => 0,
                'max_usage' => 0,
                'months_tracked' => 0,
                'total_available' => 0,
            ];
        }

        return [
            'total_usage' => $logs->sum('usage_count'),
            'avg_usage' => round($logs->avg('usage_count'), 1),
            'max_usage' => $logs->max('usage_count'),
            'months_tracked' => $logs->count(),
            'total_available' => $logs->sum('limit'),
            'usage_percentage' => $logs->sum('limit') > 0
                ? round(($logs->sum('usage_count') / $logs->sum('limit')) * 100, 1)
                : 0,
        ];
    }
}
