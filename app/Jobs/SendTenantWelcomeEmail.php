<?php

namespace App\Jobs;

use App\Mail\TenantWelcomeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class SendTenantWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $domain;
    public string $adminEmail;
    public ?string $password;

    public function __construct(string $domain, string $adminEmail, ?string $password)
    {
        $this->domain = $domain;
        $this->adminEmail = $adminEmail;
        $this->password = $password;
    }

    public function handle(): void
    {
        if (!app()->environment('production')) {
            Log::info("[Notify] Skipping welcome mail to {$this->adminEmail} for {$this->domain} (not production)");
            return;
        }
        $mailTo = $this->adminEmail;
        //$mailTo = "netzcream@gmail.com";
        Mail::to($mailTo)->send(new TenantWelcomeMail(
            domain: $this->domain,
            adminEmail: $this->adminEmail,
            password: $this->password
        ));

        Log::info("[Notify] Welcome mail sent to {$mailTo}");
    }
}
