<?php

namespace App\Mail\Tenant;

use App\Services\Tenant\BrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $mobile;
    public $messageContent;

    public function __construct($name, $email, $mobile, $messageContent)
    {
        $this->name = $name;
        $this->email = $email;
        $this->mobile = $mobile;
        $this->messageContent = $messageContent;
    }

    public function build()
    {
        $branding = BrandingService::getSafeBrandingData();
        $tenantName = $branding['brand_name'] ?? (tenant('name') ?? config('app.name'));

        return $this->from(
            'services@fittrack.com.ar',
            $tenantName
        )
            ->subject('Nuevo mensaje desde formulario de contacto')
            ->markdown('emails.tenant.contact-form')->with([
                'tenantName' => $tenantName,
                'contactEmail' => BrandingService::getContactEmail(),
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
