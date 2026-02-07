<?php

namespace App\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestTenantNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $channel;      // p.ej.: "contactos" | "prospectos"
    public string $testedEmail;  // correo que se está probando
    public string $reason;       // motivo genérico configurable

    public function __construct(string $channel, string $testedEmail, string $reason)
    {
        $this->channel     = $channel;
        $this->testedEmail = $testedEmail;
        $this->reason      = $reason;
    }

    public function build()
    {
        return $this->from(
            env('MAIL_FROM_ADDRESS', 'notifications@fittrack.com.ar'),

            tenant('name') ?? config('app.name')
        )
        ->subject("Verificación de configuración de correo de notificación de {$this->channel}")
        ->markdown('emails.tenant.test-notification')
        ->with([
            'tenantName'   => tenant('name') ?? config('app.name'),
            'channel'      => $this->channel,
            'testedEmail'  => $this->testedEmail,
            'reason'       => $this->reason,
        ]);
    }
}
