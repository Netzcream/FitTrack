<?php

namespace App\Models;

use App\Enums\TenantStatus;
use App\Models\Central\CommercialPlan;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\MaintenanceMode;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, MaintenanceMode, HasFactory;

    protected $guarded = [];
    protected $fillable = [
        'id',
        'name',
        'data',
        'status',
        'commercial_plan_id',
    ];

    protected $casts = [
        'data' => 'array',
        'status' => TenantStatus::class,
        'ssl_provisioned_at' => 'datetime',
    ];

    public function scopeWithStatus($query, TenantStatus $status)
    {
        return $query->where('status', $status->value);
    }

    public function scopeActivos($query)
    {
        return $query->where('status', TenantStatus::ACTIVE->value);
    }

    public static function hasValidSslFor(?string $domain): bool
    {
        if (!$domain) {
            return false;
        }

        $tenant = static::query()->whereHas('domains', fn($q) => $q->where('domain', $domain))->first();

        if (!$tenant || !$tenant->ssl_provisioned_at) {
            return false;
        }

        $info = static::getSslCertificateInfo($domain);
        if (!$info) return false;

        $now = time();
        $cn = $info['subject']['CN'] ?? '';

        return ($now >= ($info['validFrom_time_t'] ?? 0))
            && ($now <= ($info['validTo_time_t'] ?? 0))
            && static::certMatchesDomain($domain, $cn);
    }

    public static function sslExpirationDateFor(string $domain): ?\DateTime
    {
        $info = static::getSslCertificateInfo($domain);

        return isset($info['validTo_time_t'])
            ? new \DateTime("@{$info['validTo_time_t']}")
            : null;
    }

    public static function sslInfoFor(string $domain): ?array
    {
        return static::getSslCertificateInfo($domain);
    }

    public function mainDomain(): string
    {
        return $this->id . '.' . env('APP_DOMAIN', 'fittrack.com.ar');
    }

    protected static function getSslCertificateInfo(string $domain): ?array
    {
        $ctx = stream_context_create([
            'ssl' => [
                'capture_peer_cert_chain' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $fp = @stream_socket_client("ssl://{$domain}:443", $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $ctx);

        if (!$fp) {
            Log::warning("No se pudo conectar por SSL a {$domain}: {$errstr} ({$errno})");
            return null;
        }

        $params = stream_context_get_params($fp);
        $chain = $params['options']['ssl']['peer_certificate_chain'] ?? null;

        if (is_array($chain) && count($chain) > 0) {
            $parsed = openssl_x509_parse($chain[0]);
            if (!$parsed) {
                Log::warning("Error al parsear el certificado de {$domain}");
            }
            return $parsed ?: null;
        }

        Log::warning("No se obtuvo cadena de certificados para {$domain}");
        return null;
    }

    protected static function certMatchesDomain(string $domain, string $cn): bool
    {
        $cn = trim(str_replace('CN=', '', $cn));

        if ($domain === $cn) {
            return true;
        }

        // wildcard como *.fittrack.com.ar
        if (str_starts_with($cn, '*.') && str_ends_with($domain, substr($cn, 1))) {
            return true;
        }

        return false;
    }

    public function config(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TenantConfiguration::class, 'tenant_id', 'id');
    }

    public function getConfigAttribute()
    {
        return $this->config()->firstOrCreate();
    }

    public function plan()
    {
        return $this->belongsTo(CommercialPlan::class, 'commercial_plan_id');
    }

    /**
     * Obtiene el límite mensual de generaciones con IA según el plan comercial.
     */
    public function getAiGenerationLimit(): int
    {
        if (!$this->plan) {
            return 0;
        }

        return match($this->plan->slug) {
            'pro' => 100,
            'equipo' => 500,
            default => 0,
        };
    }

    /**
     * Obtiene el uso actual de generaciones con IA del mes.
     */
    public function getAiGenerationUsage(): array
    {
        $config = $this->config;
        $currentMonth = now()->format('Y-m');
        $data = $config->data ?? [];

        // Si es un mes nuevo, guardar histórico y resetear contador
        if (($data['ai_usage_month'] ?? null) !== $currentMonth) {
            // Guardar en histórico antes de resetear (solo si había uso previo)
            $previousMonth = $data['ai_usage_month'] ?? null;
            $previousUsage = $data['ai_usage_count'] ?? 0;

            if ($previousMonth && $previousUsage > 0) {
                \App\Models\Central\AiUsageLog::recordUsage(
                    tenantId: $this->id,
                    month: $previousMonth,
                    usageCount: $previousUsage,
                    limit: $this->getAiGenerationLimit(),
                    planSlug: $this->plan?->slug
                );
            }

            // Resetear contador para el nuevo mes
            $data['ai_usage_count'] = 0;
            $data['ai_usage_month'] = $currentMonth;
            $config->update(['data' => $data]);
        }

        $used = $data['ai_usage_count'] ?? 0;
        $limit = $this->getAiGenerationLimit();
        $available = max(0, $limit - $used);
        $percentage = $limit > 0 ? round(($used / $limit) * 100, 1) : 0;

        return [
            'used' => $used,
            'limit' => $limit,
            'available' => $available,
            'percentage' => $percentage,
            'has_limit' => $limit > 0,
            'is_exceeded' => $used >= $limit,
        ];
    }

    /**
     * Incrementa el contador de uso de IA.
     */
    public function incrementAiUsage(): void
    {
        $config = $this->config;
        $currentMonth = now()->format('Y-m');
        $data = $config->data ?? [];

        // Verificar/resetear si es mes nuevo
        if (($data['ai_usage_month'] ?? null) !== $currentMonth) {
            $data['ai_usage_count'] = 0;
            $data['ai_usage_month'] = $currentMonth;
        }

        $data['ai_usage_count'] = ($data['ai_usage_count'] ?? 0) + 1;
        $config->update(['data' => $data]);
    }

    /**
     * Verifica si el tenant puede usar generación con IA.
     */
    public function canUseAiGeneration(): bool
    {
        $usage = $this->getAiGenerationUsage();
        return $usage['has_limit'] && !$usage['is_exceeded'];
    }

    /**
     * Obtiene el historial de uso de IA.
     */
    public function getAiUsageHistory(int $months = 12): \Illuminate\Support\Collection
    {
        return \App\Models\Central\AiUsageLog::getHistory($this->id, $months);
    }

    /**
     * Obtiene estadísticas agregadas de uso de IA.
     */
    public function getAiUsageStats(): array
    {
        return \App\Models\Central\AiUsageLog::getStats($this->id);
    }
}
