<?php

namespace App\Notifications;

use App\Models\Tenant\Student;
use App\Services\Tenant\BrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeStudentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Student $student,
        public string $registrationUrl,
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $branding = $this->resolveMailBranding();
        $tenantName = $branding['tenant_name'] ?? 'FitTrack';

        return (new MailMessage)
            ->from($this->resolveFromAddress(), $tenantName)
            ->subject('Completa tu registro en ' . $tenantName)
            ->markdown('emails.tenant.student-welcome', [
                'tenantName' => $tenantName,
                'studentFirstName' => $this->student->first_name,
                'registrationUrl' => $this->registrationUrl,
                'logoUrl' => $branding['logo_url'] ?? null,
                'brandUrl' => $branding['brand_url'] ?? $this->resolveBrandUrlFromRegistrationUrl(),
                'colorBase' => $branding['color_base'] ?? '#263d83',
                'colorDark' => $branding['color_dark'] ?? '#1d2d5e',
                'colorLight' => $branding['color_light'] ?? '#f9fafb',
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Cuenta creada',
            'message' => 'Tu cuenta fue creada y se envio un email para completar el registro.',
            'student_id' => $this->student->id,
            'student_name' => $this->student->full_name,
            'type' => 'student_created',
            'icon' => 'user-plus',
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
                'brand_url' => $this->resolveBrandUrlFromRegistrationUrl(),
                'color_base' => $branding['color_base'] ?? '#263d83',
                'color_dark' => $branding['color_dark'] ?? '#1d2d5e',
                'color_light' => $branding['color_light'] ?? '#f9fafb',
            ];
        } catch (\Throwable $exception) {
            return [
                'tenant_name' => 'FitTrack',
                'logo_url' => null,
                'brand_url' => $this->resolveBrandUrlFromRegistrationUrl(),
                'color_base' => '#263d83',
                'color_dark' => '#1d2d5e',
                'color_light' => '#f9fafb',
            ];
        }
    }

    private function resolveBrandUrlFromRegistrationUrl(): string
    {
        $scheme = parse_url($this->registrationUrl, PHP_URL_SCHEME);
        $host = parse_url($this->registrationUrl, PHP_URL_HOST);
        $port = parse_url($this->registrationUrl, PHP_URL_PORT);

        if (is_string($scheme) && $scheme !== '' && is_string($host) && $host !== '') {
            $origin = $scheme . '://' . $host;

            if (is_int($port)) {
                $origin .= ':' . $port;
            }

            return $origin;
        }

        return 'https://fittrack.com.ar';
    }

    private function resolveFromAddress(): string
    {
        try {
            $configured = config('mail.from.address');
            if (is_string($configured) && trim($configured) !== '') {
                return trim($configured);
            }
        } catch (\Throwable $exception) {
            // fallback below
        }

        return 'notifications@fittrack.com.ar';
    }
}
