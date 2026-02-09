<?php

namespace App\Notifications;

use App\Models\Tenant\StudentPlanAssignment;
use App\Services\Tenant\BrandingService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public StudentPlanAssignment $assignment,
        public ?string $lastCompletedAtIso = null,
        public array $mailBranding = []
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $branding = $this->resolveMailBranding();
        $tenantName = $branding['tenant_name'] ?? 'FitTrack';
        $studentName = $this->assignment->student?->first_name ?? 'Alumno';
        $workoutUrl = route('tenant.student.workout-today');
        $pdfUrl = route('tenant.student.download-plan', $this->assignment->uuid);

        return (new MailMessage)
            ->from($this->resolveFromAddress(), $tenantName)
            ->subject('Recordatorio de sesion - ' . $tenantName)
            ->markdown('emails.tenant.session-reminder', [
                'tenantName' => $tenantName,
                'logoUrl' => $branding['logo_url'] ?? null,
                'brandUrl' => $branding['brand_url'] ?? $this->resolveBrandUrlFromUrl($workoutUrl),
                'colorBase' => $branding['color_base'] ?? '#263d83',
                'colorDark' => $branding['color_dark'] ?? '#1d2d5e',
                'colorLight' => $branding['color_light'] ?? '#f9fafb',
                'studentFirstName' => $studentName,
                'planName' => $this->assignment->name,
                'workoutUrl' => $workoutUrl,
                'pdfUrl' => $pdfUrl,
                'lastCompletedAt' => $this->formattedLastCompletedAt(),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Recordatorio de sesion',
            'message' => 'Te toca entrenar hoy. Plan: "' . $this->assignment->name . '"',
            'type' => 'session_reminder',
            'assignment_id' => $this->assignment->id,
            'assignment_uuid' => $this->assignment->uuid,
            'action_url' => route('tenant.student.workout-today'),
            'pdf_url' => route('tenant.student.download-plan', $this->assignment->uuid),
            'last_completed_at' => $this->lastCompletedAtIso,
        ];
    }

    private function formattedLastCompletedAt(): ?string
    {
        if (! is_string($this->lastCompletedAtIso) || trim($this->lastCompletedAtIso) === '') {
            return null;
        }

        try {
            return Carbon::parse($this->lastCompletedAtIso)->format('d/m/Y H:i');
        } catch (\Throwable $exception) {
            return null;
        }
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

    private function resolveBrandUrlFromUrl(string $url): string
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);

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
        $configured = config('mail.from.address');

        if (is_string($configured) && trim($configured) !== '') {
            return trim($configured);
        }

        return 'notifications@fittrack.com.ar';
    }
}
