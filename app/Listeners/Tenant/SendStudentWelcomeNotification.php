<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\StudentCreated;
use App\Notifications\WelcomeStudentNotification;
use App\Services\Tenant\BrandingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class SendStudentWelcomeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(StudentCreated $event): void
    {
        $student = $event->student;
        $registrationUrl = $event->registrationUrl;
        $initializedByListener = $this->initializeTenantIfNeeded($event->tenantId);

        try {
            Log::info('Enviando notificacion de bienvenida a estudiante', [
                'student_id' => $student->id,
                'student_name' => $student->full_name,
                'student_email' => $student->email,
                'created_by' => $event->createdBy,
                'tenant_id' => $event->tenantId,
            ]);

            if (! $registrationUrl) {
                $user = $student->user;

                if (! $user || ! $user->email) {
                    Log::warning('No se pudo enviar onboarding de alumno: falta user/email asociado', [
                        'student_id' => $student->id,
                        'student_email' => $student->email,
                    ]);

                    return;
                }

                $token = Password::broker()->createToken($user);
                $registrationUrl = route('tenant.password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ]);
            }

            if ($student->email) {
                $student->notify(new WelcomeStudentNotification(
                    $student,
                    $registrationUrl,
                    $this->buildMailBranding($registrationUrl)
                ));
            }
        } finally {
            if ($initializedByListener) {
                tenancy()->end();
            }
        }

        // TODO: Opcionalmente notificar al trainer que lo creo
        // if ($event->createdBy) {
        //     $trainer = User::find($event->createdBy);
        //     $trainer?->notify(new StudentCreatedNotification($student));
        // }
    }

    private function initializeTenantIfNeeded(?string $tenantId): bool
    {
        if (! is_string($tenantId) || trim($tenantId) === '') {
            return false;
        }

        try {
            $currentTenantId = tenancy()->initialized ? (string) tenancy()->tenant?->id : null;
            if ($currentTenantId === $tenantId) {
                return false;
            }

            tenancy()->initialize($tenantId);
            return true;
        } catch (\Throwable $exception) {
            Log::warning('No se pudo inicializar tenancy en listener de bienvenida', [
                'tenant_id' => $tenantId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function buildMailBranding(string $registrationUrl): array
    {
        $branding = BrandingService::getSafeBrandingData();

        return [
            'tenant_name' => $branding['brand_name'] ?? 'FitTrack',
            'logo_url' => $branding['logo_url'] ?? null,
            'color_base' => $branding['color_base'] ?? '#263d83',
            'color_dark' => $branding['color_dark'] ?? '#1d2d5e',
            'color_light' => $branding['color_light'] ?? '#f9fafb',
            'brand_url' => $this->resolveBrandUrl($registrationUrl),
        ];
    }

    private function resolveBrandUrl(string $registrationUrl): string
    {
        $scheme = parse_url($registrationUrl, PHP_URL_SCHEME);
        $host = parse_url($registrationUrl, PHP_URL_HOST);
        $port = parse_url($registrationUrl, PHP_URL_PORT);

        if (is_string($scheme) && $scheme !== '' && is_string($host) && $host !== '') {
            $origin = $scheme . '://' . $host;

            if (is_int($port)) {
                $origin .= ':' . $port;
            }

            return $origin;
        }

        $appUrl = (string) config('app.url', '');
        return rtrim($appUrl, '/');
    }

    /**
     * Handle a job failure.
     */
    public function failed(StudentCreated $event, \Throwable $exception): void
    {
        Log::error('Error al enviar notificacion de estudiante creado', [
            'student_id' => $event->student->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
