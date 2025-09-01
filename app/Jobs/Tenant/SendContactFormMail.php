<?php

namespace App\Jobs\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\Tenant\ContactFormSubmitted;

class SendContactFormMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // ===== Retries & backoff =====
    public int $tries = 8;

    public function backoff(): array
    {
        // 1m, 5m, 10m, 30m, 1h, 2h, 4h, 6h
        return [60, 300, 600, 1800, 3600, 7200, 14400, 21600];
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(24);
    }

    // ===== Payload =====
    protected string $name;
    protected string $email;
    protected ?string $mobile;
    protected string $messageContent;
    protected ?string $tenantId;

    public function __construct(string $name, string $email, ?string $mobile, string $messageContent)
    {
        $this->name = $name;
        $this->email = $email;
        $this->mobile = $mobile;
        $this->messageContent = $messageContent;
        $this->tenantId = tenant('id'); // capturamos el tenant activo al despachar
    }

    public function handle(): void
    {
        $initialized = false;

        try {
            if ($this->tenantId) {
                tenancy()->initialize($this->tenantId);
                $initialized = true;
            }

            $recipient = tenant('contact_email') ?? 'services@fittrack.com.ar';

            Log::info('[MAIL] Enviando ContactFormSubmitted', [
                'tenant'   => $this->tenantId,
                'to'       => $recipient,
                'attempt'  => $this->attempts(),
            ]);

            Mail::to($recipient)->send(
                new ContactFormSubmitted(
                    $this->name,
                    $this->email,
                    $this->mobile,
                    $this->messageContent
                )
            );

            Log::info('[MAIL] Envío OK', [
                'tenant'  => $this->tenantId,
                'attempt' => $this->attempts(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[MAIL] Falló envío, se reintentará', [
                'tenant'  => $this->tenantId,
                'attempt' => $this->attempts(),
                'class'   => get_class($e),
                'msg'     => $e->getMessage(),
            ]);
            throw $e; // reintento según $tries/backoff
        } finally {
            if ($initialized) {
                tenancy()->end();
            }
        }
    }
}
