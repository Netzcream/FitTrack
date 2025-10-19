<?php

namespace App\Jobs\Tenant;

use App\Mail\Tenant\TestTenantNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTestTenantNotificationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function backoff(): array
    {
        // reintentos progresivos
        return [60, 300, 900, 1800, 3600];
    }

    protected string $tenantId;
    protected string $channel;     // "contactos" | "prospectos" (o los que sumemos luego)
    protected string $targetEmail; // correo a testear (lo que está cargado en el input)
    protected string $reason;      // motivo genérico

    public function __construct(string $channel, string $targetEmail, string $reason)
    {
        $this->tenantId    = (string) tenant('id');
        $this->channel     = $channel;
        $this->targetEmail = $targetEmail;
        $this->reason      = $reason;
    }

    public function handle(): void
    {
        $initialized = false;

        try {
            if ($this->tenantId) {
                tenancy()->initialize($this->tenantId);
                $initialized = true;
            }

            Log::info('[MAIL][TEST] Enviando verificación', [
                'tenant'  => $this->tenantId,
                'channel' => $this->channel,
                'to'      => $this->targetEmail,
                'attempt' => $this->attempts(),
            ]);

            Mail::to($this->targetEmail)->send(
                new TestTenantNotificationMail(
                    channel: $this->channel,
                    testedEmail: $this->targetEmail,
                    reason: $this->reason
                )
            );

            Log::info('[MAIL][TEST] Envío OK', [
                'tenant'  => $this->tenantId,
                'channel' => $this->channel,
                'to'      => $this->targetEmail,
                'attempt' => $this->attempts(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[MAIL][TEST] Falló envío, se reintenta', [
                'tenant'  => $this->tenantId,
                'channel' => $this->channel,
                'to'      => $this->targetEmail,
                'attempt' => $this->attempts(),
                'class'   => get_class($e),
                'msg'     => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            if ($initialized) {
                tenancy()->end();
            }
        }
    }
}
