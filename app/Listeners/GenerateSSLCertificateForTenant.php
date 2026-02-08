<?php

namespace App\Listeners;

use App\Events\TenantCreatedSuccessfully;
use Illuminate\Support\Facades\Log;
use App\Jobs\GenerateTenantSSLCertificate;

class GenerateSSLCertificateForTenant
{
    public function handle(TenantCreatedSuccessfully $event): void
    {

        Log::info("[SSL] Job start GenerateTenantSSLCertificate", [
            'domain' => $event->domain,
            'env' => app()->environment(),
        ]);

        if (!app()->environment('production')) {
            Log::info("[SSL] Skipping SSL generation for {$event->domain} (not production)");
            return;
        }

        GenerateTenantSSLCertificate::dispatch($event->domain)
            ->onConnection('database')
            ->onQueue('default');

        Log::info("[SSL] Queued GenerateTenantSSLCertificate", [
            'domain' => $event->domain,
            'connection' => 'database',
            'queue' => 'default',
        ]);
    }
}
