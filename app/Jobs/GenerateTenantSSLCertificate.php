<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;

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
        Log::info('[SSL] Job handle GenerateTenantSSLCertificate', [
            'domain' => $this->domain,
            'env' => app()->environment(),
            'connection' => $this->connection,
            'queue' => $this->queue,
        ]);

        if (!app()->environment('production')) {
            Log::info("[SSL] Skipping SSL generation for {$this->domain} (not production)");
            return;
        }

        $confPath = "/etc/apache2/sites-available/{$this->domain}.conf";
        $site80 = "{$this->domain}.conf";

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

            $this->writeRootOwnedFile($confPath, $conf);
            $this->runCommandOrFail(
                'sudo a2ensite ' . escapeshellarg($site80),
                "a2ensite {$site80}"
            );
            $this->runCommandOrFail('sudo systemctl reload apache2', 'reload apache2 (80)');

            Log::info("[SSL] VirtualHost activado para {$this->domain}");
        }

        $certbotCommand =
            'sudo certbot certonly --apache -d '
            . escapeshellarg($this->domain)
            . ' --non-interactive --agree-tos --email admin@fittrack.com.ar';

        Log::info('[SSL] Ejecutando certbot', ['command' => $certbotCommand]);
        $certbotResult = Process::run($certbotCommand);

        if (!$certbotResult->successful()) {
            Log::error("[SSL] Error generando SSL para {$this->domain}", [
                'exit_code' => $certbotResult->exitCode(),
                'output' => trim($certbotResult->output()),
                'error_output' => trim($certbotResult->errorOutput()),
            ]);

            throw new RuntimeException(
                "Certbot failed for {$this->domain} with code {$certbotResult->exitCode()}"
            );
        }

        Log::info("[SSL] Certificado generado correctamente para {$this->domain}");

        $sslConfPath = "/etc/apache2/sites-available/{$this->domain}-le-ssl.conf";
        $site443 = "{$this->domain}-le-ssl.conf";

        if (!file_exists($sslConfPath)) {
            Log::info("[SSL] Generando VirtualHost SSL para {$this->domain}");
            $apacheLogDirToken = '${APACHE_LOG_DIR}';

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

    ErrorLog {$apacheLogDirToken}/{$this->domain}_error.log
    CustomLog {$apacheLogDirToken}/{$this->domain}_access.log combined
</VirtualHost>
</IfModule>
CONF;

            $this->writeRootOwnedFile($sslConfPath, $sslConf);
            $this->runCommandOrFail(
                'sudo a2ensite ' . escapeshellarg($site443),
                "a2ensite {$site443}"
            );
            $this->runCommandOrFail('sudo systemctl reload apache2', 'reload apache2 (443)');

            Log::info("[SSL] VirtualHost SSL activado para {$this->domain}");
        }

        $tenant = \App\Models\Tenant::whereHas('domains', fn($q) => $q->where('domain', $this->domain))->first();
        if ($tenant) {
            $tenant->update(['ssl_provisioned_at' => now()]);
            Log::info("[SSL] ssl_provisioned_at actualizado para {$this->domain}");
        }
    }

    private function writeRootOwnedFile(string $path, string $content): void
    {
        $tmpDir = storage_path('app/ssl');
        if (!is_dir($tmpDir) && !mkdir($tmpDir, 0755, true) && !is_dir($tmpDir)) {
            throw new RuntimeException("[SSL] No se pudo crear directorio temporal: {$tmpDir}");
        }

        $tmpFile = $tmpDir . '/' . sha1($path . '|' . microtime(true)) . '.conf';
        if (file_put_contents($tmpFile, $content) === false) {
            throw new RuntimeException("[SSL] No se pudo escribir archivo temporal: {$tmpFile}");
        }

        $command = sprintf(
            'sudo install -m 644 %s %s',
            escapeshellarg($tmpFile),
            escapeshellarg($path)
        );

        $result = Process::run($command);
        @unlink($tmpFile);

        if (!$result->successful()) {
            throw new RuntimeException(
                "[SSL] No se pudo copiar archivo a {$path}: " . trim($result->errorOutput() . PHP_EOL . $result->output())
            );
        }
    }

    private function runCommandOrFail(string $command, string $label): void
    {
        Log::info("[SSL] Ejecutando comando {$label}", ['command' => $command]);

        $result = Process::run($command);

        if (!$result->successful()) {
            throw new RuntimeException(
                "[SSL] Fallo {$label}: " . trim($result->errorOutput() . PHP_EOL . $result->output())
            );
        }
    }
}
