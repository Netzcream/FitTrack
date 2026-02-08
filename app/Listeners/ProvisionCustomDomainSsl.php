<?php

namespace App\Listeners;

use App\Events\TenantCustomDomainAttached;
use Illuminate\Support\Facades\Log;
use App\Jobs\GenerateTenantSSLCertificate;

class ProvisionCustomDomainSsl
{
    public function handle(TenantCustomDomainAttached $event): void
    {
        Log::info("[SSL] Listener hit: ProvisionCustomDomainSsl", [
            'domain' => $event->domain,
            'env' => app()->environment(),
        ]);

        // ✅ ÚNICO criterio para “no reprovisionar”: cert local ya presente
        if ($this->hasLocalLetsEncryptCert($event->domain)) {
            Log::info("[SSL] Cert local ya presente, no se reprovisiona", ['domain' => $event->domain]);
            return;
        }

        if (! app()->isProduction()) {
            Log::info("[SSL] Not production, skip", ['domain' => $event->domain]);
            return;
        }

        Log::info("[SSL] Queueing job GenerateTenantSSLCertificate", ['domain' => $event->domain]);
        GenerateTenantSSLCertificate::dispatch($event->domain)
            ->onConnection('database')
            ->onQueue('default');

        Log::info("[SSL] Queued GenerateTenantSSLCertificate", [
            'domain' => $event->domain,
            'connection' => 'database',
            'queue' => 'default',
        ]);
    }

    private function hasLocalLetsEncryptCert(string $domain): bool
    {
        if (file_exists("/etc/letsencrypt/live/{$domain}/fullchain.pem")
            || file_exists("/etc/letsencrypt/renewal/{$domain}.conf")) {
            return true;
        }
        $live  = glob("/etc/letsencrypt/live/{$domain}-*/fullchain.pem") ?: [];
        $renew = glob("/etc/letsencrypt/renewal/{$domain}-*.conf") ?: [];
        return !empty($live) || !empty($renew);
    }
}
