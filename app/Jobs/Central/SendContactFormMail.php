<?php

namespace App\Jobs\Central;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\Central\ContactForm;

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

    public function __construct(string $name, string $email, ?string $mobile, string $messageContent)
    {
        $this->name = $name;
        $this->email = $email;
        $this->mobile = $mobile;
        $this->messageContent = $messageContent;
    }

    public function handle(): void
    {
        $initialized = false;

        try {

            $recipient = 'notifications@fittrack.com.ar';
            if (env('APP_ENV') != 'production') {
                $recipient = 'info@fittrack.com.ar';

            }


            Log::info('[MAIL] Enviando ContactForm', [
                'to'       => $recipient,
                'attempt'  => $this->attempts(),
            ]);

            Mail::to($recipient)->send(
                new ContactForm(
                    $this->name,
                    $this->email,
                    $this->mobile,
                    $this->messageContent
                )
            );

            Log::info('[MAIL] Envío OK', [
                'attempt' => $this->attempts(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[MAIL] Falló envío, se reintentará', [
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
