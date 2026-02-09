<?php

namespace App\Notifications;

use App\Models\Tenant\StudentPlanAssignment;
use App\Services\Tenant\BrandingService;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingPlanActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public StudentPlanAssignment $assignment,
        public string $activationType,
        public array $mailBranding = []
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $wasAutomatic = $this->activationType === 'automatic';
        $branding = $this->resolveMailBranding();
        $tenantName = $branding['tenant_name'] ?? 'FitTrack';

        $planUrl = $this->tenantUrl(route('tenant.student.dashboard', [], false));
        $pdfUrl = $this->tenantUrl(route('tenant.student.download-plan', $this->assignment->uuid, false));

        return (new MailMessage)
            ->from($this->resolveFromAddress(), $tenantName)
            ->subject($wasAutomatic
                ? 'Tu nuevo plan ya esta activo en ' . $tenantName
                : 'Nuevo plan asignado en ' . $tenantName)
            ->markdown('emails.tenant.plan-activated', [
                'tenantName' => $tenantName,
                'logoUrl' => $branding['logo_url'] ?? null,
                'brandUrl' => $branding['brand_url'] ?? $this->tenantUrl('/'),
                'colorBase' => $branding['color_base'] ?? '#263d83',
                'colorDark' => $branding['color_dark'] ?? '#1d2d5e',
                'colorLight' => $branding['color_light'] ?? '#f9fafb',
                'studentFirstName' => $this->assignment->student?->first_name ?? 'Alumno',
                'planName' => $this->assignment->name,
                'startsAt' => $this->assignment->starts_at?->format('d/m/Y'),
                'endsAt' => $this->assignment->ends_at?->format('d/m/Y'),
                'durationDays' => $this->resolveDurationDays(),
                'wasAutomatic' => $wasAutomatic,
                'planUrl' => $planUrl,
                'pdfUrl' => $pdfUrl,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $planUrl = $this->tenantUrl(route('tenant.student.dashboard', [], false));
        $pdfUrl = $this->tenantUrl(route('tenant.student.download-plan', $this->assignment->uuid, false));

        return [
            'title' => $this->activationType === 'automatic' ? 'Plan activado automaticamente' : 'Nuevo plan asignado',
            'message' => 'El plan "' . $this->assignment->name . '" ya esta disponible para vos',
            'assignment_id' => $this->assignment->id,
            'assignment_uuid' => $this->assignment->uuid,
            'plan_name' => $this->assignment->name,
            'starts_at' => $this->assignment->starts_at?->format('Y-m-d'),
            'ends_at' => $this->assignment->ends_at?->format('Y-m-d'),
            'activation_type' => $this->activationType,
            'type' => $this->activationType === 'automatic' ? 'plan_activated' : 'plan_assigned',
            'icon' => 'calendar-check',
            'action_url' => $planUrl,
            'pdf_url' => $pdfUrl,
        ];
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
        if (!str_starts_with($path, '/')) {
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

    private function resolveDurationDays(): ?int
    {
        if (! $this->assignment->starts_at || ! $this->assignment->ends_at) {
            return null;
        }

        return $this->assignment->starts_at->diffInDays($this->assignment->ends_at);
    }
}
