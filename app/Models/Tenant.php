<?php

namespace App\Models;

use App\Enums\TenantStatus;
use App\Models\Central\Plan;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\MaintenanceMode;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Illuminate\Support\Facades\Log;


class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, MaintenanceMode;

    protected $fillable = [
        'plan_id',
    ];

    protected $casts = [
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
        return $this->belongsTo(Plan::class);
    }
}
