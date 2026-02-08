<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateTenantSSLCertificate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $domain;

    public string $connection = 'database';
    public string $queue = 'default';

    public $tries = 3;
    public $timeout = 60;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    public function handle(): void
    {
        if (!app()->environment('production')) {
            Log::info("[SSL] Skipping SSL generation for {$this->domain} (not production)");
            return;
        }

        $confPath = "/etc/apache2/sites-available/{$this->domain}.conf";

        if (!file_exists($confPath)) {
            Log::info("[SSL] Generando VirtualHost temporal para {$this->domain}");

            $conf = <<<CONF
<VirtualHost *:80>
    ServerName {$this->domain}
    DocumentRoot /var/www/fittrack.com.ar/public

    <Directory /var/www/fittrack.com.ar/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
CONF;

            file_put_contents($confPath, $conf);

            Process::run("sudo a2ensite {$this->domain}.conf");
            Process::run("sudo systemctl reload apache2");

            Log::info("[SSL] VirtualHost activado para {$this->domain}");
        }



        $cmd = "sudo certbot certonly --apache -d {$this->domain} --non-interactive --agree-tos --email admin@fittrack.com.ar";
        Log::info("[SSL] Ejecutando certbot: {$cmd}");
        exec($cmd, $output, $code);

        if ($code === 0) {
            Log::info("[SSL] Certificado generado correctamente para {$this->domain}");

            $sslConfPath = "/etc/apache2/sites-available/{$this->domain}-le-ssl.conf";

            if (!file_exists($sslConfPath)) {
                Log::info("[SSL] Generando VirtualHost SSL para {$this->domain}");

                $sslConf = <<<CONF
<IfModule mod_ssl.c>
<VirtualHost *:443>
    ServerName {$this->domain}
    DocumentRoot /var/www/fittrack.com.ar/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/{$this->domain}/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/{$this->domain}/privkey.pem

    <Directory /var/www/fittrack.com.ar/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/{$this->domain}_error.log
    CustomLog \${APACHE_LOG_DIR}/{$this->domain}_access.log combined
</VirtualHost>
</IfModule>
CONF;

                file_put_contents($sslConfPath, $sslConf);

                Process::run("sudo a2ensite {$this->domain}-le-ssl.conf");
                Process::run("sudo systemctl reload apache2");

                Log::info("[SSL] VirtualHost SSL activado para {$this->domain}");
            }
            $tenant = \App\Models\Tenant::whereHas('domains', fn($q) => $q->where('domain', $this->domain))->first();
            if ($tenant) {
                $tenant->update(['ssl_provisioned_at' => now()]);
                Log::info("[SSL] ssl_provisioned_at actualizado para {$this->domain}");
            }

        } else {
            Log::error("[SSL] Error generando SSL para {$this->domain}. Código: {$code}", $output);
            Log::error("[SSL] Output completo:", ['output' => implode(PHP_EOL, $output)]);
            throw new \RuntimeException("Certbot failed for {$this->domain} with code {$code}");
        }

    }
}
