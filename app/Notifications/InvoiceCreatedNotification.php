<?php

namespace App\Notifications;

use App\Models\Tenant;
use App\Models\Tenant\Invoice;
use App\Services\Tenant\BrandingService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invoice $invoice,
        public ?string $createdAtIso = null,
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
        $this->invoice->loadMissing('student', 'planAssignment.plan');

        $branding = $this->resolveMailBranding();
        $tenantName = $branding['tenant_name'] ?? 'FitTrack';
        $studentFirstName = $this->invoice->student?->first_name ?? 'Alumno';
        $paymentsUrl = $this->tenantUrl(route('tenant.student.payments', [], false));

        return (new MailMessage)
            ->from($this->resolveFromAddress(), $tenantName)
            ->subject('Nuevo invoice en ' . $tenantName)
            ->markdown('emails.tenant.invoice-created', [
                'tenantName' => $tenantName,
                'logoUrl' => $branding['logo_url'] ?? null,
                'brandUrl' => $branding['brand_url'] ?? $this->tenantUrl('/'),
                'colorBase' => $branding['color_base'] ?? '#263d83',
                'colorDark' => $branding['color_dark'] ?? '#1d2d5e',
                'colorLight' => $branding['color_light'] ?? '#f9fafb',
                'studentFirstName' => $studentFirstName,
                'amount' => $this->invoice->formatted_amount,
                'dueDate' => $this->invoice->due_date?->format('d/m/Y'),
                'planName' => $this->resolvePlanName(),
                'label' => data_get($this->invoice->meta, 'label'),
                'notes' => data_get($this->invoice->meta, 'notes'),
                'invoiceNumber' => $this->invoice->invoice_number ?? null,
                'createdAt' => $this->formattedCreatedAt(),
                'paymentsUrl' => $paymentsUrl,
            ]);
    }

    private function formattedCreatedAt(): ?string
    {
        if (! is_string($this->createdAtIso) || trim($this->createdAtIso) === '') {
            return null;
        }

        try {
            return Carbon::parse($this->createdAtIso)->format('d/m/Y H:i');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function resolvePlanName(): ?string
    {
        return data_get($this->invoice->meta, 'plan_name')
            ?? $this->invoice->planAssignment?->plan?->name
            ?? $this->invoice->planAssignment?->name;
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
