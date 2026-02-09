<?php

namespace App\Mail\Tenant;

use App\Services\Tenant\BrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestTenantNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $channel;
    public string $testedEmail;
    public string $reason;

    public function __construct(string $channel, string $testedEmail, string $reason)
    {
        $this->channel = $channel;
        $this->testedEmail = $testedEmail;
        $this->reason = $reason;
    }

    public function build()
    {
        $branding = BrandingService::getSafeBrandingData();
        $tenantName = $branding['brand_name'] ?? (tenant('name') ?? config('app.name'));

        return $this->from(
            env('MAIL_FROM_ADDRESS', 'notifications@fittrack.com.ar'),
            $tenantName
        )
            ->subject("Verificación de configuración de correo de notificación de {$this->channel}")
            ->markdown('emails.tenant.test-notification')
            ->with([
                'tenantName' => $tenantName,
                'channel' => $this->channel,
                'testedEmail' => $this->testedEmail,
                'reason' => $this->reason,
                'brandUrl' => $this->resolveTenantBrandUrl(),
                'logoUrl' => $branding['logo_url'] ?? null,
            ]);
    }

    private function resolveTenantBrandUrl(): string
    {
        $tenant = tenant();
        if (!$tenant) {
            return rtrim((string) config('app.url', ''), '/');
        }

        $domain = $tenant->domains()->orderBy('id')->value('domain');
        if (!is_string($domain) || trim($domain) === '') {
            $domain = $tenant->mainDomain();
        }

        if (!is_string($domain) || trim($domain) === '') {
            return rtrim((string) config('app.url', ''), '/');
        }

        $domain = trim($domain);
        if (str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')) {
            return rtrim($domain, '/');
        }

        return $this->resolveScheme() . '://' . ltrim($domain, '/');
    }

    private function resolveScheme(): string
    {
        $appUrlScheme = parse_url((string) config('app.url'), PHP_URL_SCHEME);
        if (is_string($appUrlScheme) && in_array($appUrlScheme, ['http', 'https'], true)) {
            return $appUrlScheme;
        }

        return app()->environment('local') ? 'http' : 'https';
    }
}
