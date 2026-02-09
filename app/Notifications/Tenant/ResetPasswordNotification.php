<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant;
use App\Services\Tenant\BrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token,
        public array $mailBranding = []
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $branding = $this->resolveMailBranding();
        $tenantName = $branding['tenant_name'] ?? 'FitTrack';
        $resetUrl = $this->resetUrl($notifiable);
        $expireMinutes = (int) config(
            'auth.passwords.' . config('auth.defaults.passwords') . '.expire',
            60
        );

        return (new MailMessage)
            ->from($this->resolveFromAddress(), $tenantName)
            ->subject('Notificacion de restablecimiento de clave - ' . $tenantName)
            ->markdown('emails.tenant.password-reset', [
                'tenantName' => $tenantName,
                'logoUrl' => $branding['logo_url'] ?? null,
                'brandUrl' => $branding['brand_url'] ?? $this->tenantUrl('/'),
                'colorBase' => $branding['color_base'] ?? '#263d83',
                'colorDark' => $branding['color_dark'] ?? '#1d2d5e',
                'colorLight' => $branding['color_light'] ?? '#f9fafb',
                'resetUrl' => $resetUrl,
                'expireMinutes' => $expireMinutes,
            ]);
    }

    private function resetUrl(object $notifiable): string
    {
        $path = route('tenant.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false);

        return $this->tenantUrl($path);
    }

    private function resolveMailBranding(): array
    {
        if (! empty($this->mailBranding)) {
            return $this->mailBranding;
        }

        try {
            $branding = BrandingService::getSafeBrandingData();

            return [
                'tenant_name' => $branding['brand_name'] ?? 'FitTrack',
                'logo_url' => $branding['logo_url'] ?? null,
                'brand_url' => null,
                'color_base' => $branding['color_base'] ?? '#263d83',
                'color_dark' => $branding['color_dark'] ?? '#1d2d5e',
                'color_light' => $branding['color_light'] ?? '#f9fafb',
            ];
        } catch (\Throwable $exception) {
            return [
                'tenant_name' => 'FitTrack',
                'logo_url' => null,
                'brand_url' => null,
                'color_base' => '#263d83',
                'color_dark' => '#1d2d5e',
                'color_light' => '#f9fafb',
            ];
        }
    }

    private function tenantUrl(string $path): string
    {
        if (! str_starts_with($path, '/')) {
            $path = '/' . ltrim($path, '/');
        }

        return rtrim($this->resolveTenantBaseUrl(), '/') . $path;
    }

    private function resolveTenantBaseUrl(): string
    {
        $tenant = tenant();

        if ($tenant instanceof Tenant) {
            try {
                $domain = $tenant->domains()->orderBy('id')->value('domain');
                if (is_string($domain) && trim($domain) !== '') {
                    $domain = trim($domain);

                    if (str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')) {
                        return rtrim($domain, '/');
                    }

                    return $this->resolveScheme() . '://' . ltrim($domain, '/');
                }
            } catch (\Throwable $exception) {
                // fallback below
            }
        }

        $appUrl = trim((string) config('app.url', 'https://fittrack.com.ar'));
        if ($appUrl === '') {
            return 'https://fittrack.com.ar';
        }

        return rtrim($appUrl, '/');
    }

    private function resolveScheme(): string
    {
        $scheme = parse_url((string) config('app.url', ''), PHP_URL_SCHEME);
        if (is_string($scheme) && in_array($scheme, ['http', 'https'], true)) {
            return $scheme;
        }

        return app()->environment('local') ? 'http' : 'https';
    }

    private function resolveFromAddress(): string
    {
        $configured = config('mail.from.address');

        if (is_string($configured) && trim($configured) !== '') {
            return trim($configured);
        }

        return 'notifications@fittrack.com.ar';
    }
}
