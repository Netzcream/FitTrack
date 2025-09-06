<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;
use App\Enums\TenantStatus;
use Carbon\Carbon;

class MaintainTenantSSL extends Command
{
    //protected $signature = 'ssl:maintain';
    protected $signature = 'ssl:maintain {--list-orphans : Solo listar posibles certificados/vhosts huérfanos}';
    protected $description = 'Renueva certificados SSL y elimina los de tenants inactivos hace más de 30 días';

    public function handle(): int
    {
        Log::info('[SSL] ssl:maintain START', ['list_orphans' => $this->option('list-orphans')]);
        if ($this->option('list-orphans')) {
            $this->info('[SSL] Modo listado de huérfanos (no se tocan certificados ni vhosts).');
            Log::info('[SSL] Orphans report BEGIN');
            $this->reportOrphanCertificatesAndVhosts();
            Log::info('[SSL] Orphans report END');
            return Command::SUCCESS;
        }
        $this->info('[SSL] Ejecutando certbot renew...');
        exec('sudo certbot renew --quiet', $output, $renewCode);

        if ($renewCode !== 0) {
            $this->error('[SSL] Falló la renovación automática de certificados.');
            Log::error('[SSL] Falló certbot renew', $output);
            return Command::FAILURE;
        }

        $this->info('[SSL] Renovación completada.');
        Log::info('[SSL] certbot renew OK');
        $this->cleanExpiredTenantCertificates();
        Log::info('[SSL] ssl:maintain END');
        return Command::SUCCESS;
    }

    protected function cleanExpiredTenantCertificates(): void
    {
        $this->info('[SSL] Buscando certificados obsoletos...');

        $dirs = glob('/etc/letsencrypt/live/*', GLOB_ONLYDIR);
        $now = now();

        foreach ($dirs as $dir) {
            $certName = basename($dir);

            // Sólo chequeamos subdominios de nuestro dominio
            if (!str_ends_with($certName, '.' . env('APP_DOMAIN'))) {
                continue;
            }

            $tenantId = explode('.', $certName)[0];
            $tenant = Tenant::find($tenantId);

            if (! $tenant) {
                $this->warn("❌ No se encontró el tenant para dominio {$certName}, no se elimina.");
                continue;
            }

            if ($tenant->status === TenantStatus::DELETED && $tenant->updated_at->lt(Carbon::now()->subDays(30))) {
                $this->warn("🧹 Eliminando certificado de tenant dado de baja: {$certName}");
                exec("sudo certbot delete --cert-name {$certName} --quiet", $delOut, $delCode);

                if ($delCode === 0) {
                    Log::info("[SSL] Certificado eliminado para {$certName}");
                } else {
                    Log::error("[SSL] Falló la eliminación del certificado de {$certName}", $delOut);
                }
            }
        }
    }


    protected function reportOrphanCertificatesAndVhosts(): void
    {
        $this->info('[SSL] Buscando certificados/vhosts huérfanos (solo reporte)...');

        // 1) Armar lista de dominios “conocidos” desde los modelos (Tenant + HasDomains)
        $tenants = \App\Models\Tenant::with('domains:id,tenant_id,domain')->get();

        $known = [];
        foreach ($tenants as $t) {
            foreach ($t->domains as $d) {
                $known[] = strtolower($d->domain);
            }
        }
        $known = array_values(array_unique($known));

        // Normalizar nombres que Certbot puede versionar (pepe.com.ar-0001)
        $normalize = static fn(string $d) => preg_replace('/-\d+$/', '', $d);
        $knownNormalized = array_map($normalize, $known);

        // 2) Dominios “protegidos” (no reportar como huérfanos)
        $root = strtolower((string) env('APP_DOMAIN', 'luniqo.com'));
        $protected = array_filter([
            $root,
            "www.$root",
            // agregá aquí otros hostnames que quieras preservar siempre
        ]);

        // 3) Recorrer certs existentes en el servidor
        $dirs = glob('/etc/letsencrypt/live/*', GLOB_ONLYDIR) ?: [];

        foreach ($dirs as $dir) {
            $certName = strtolower(basename($dir));
            $norm     = $normalize($certName);

            // nunca marcar protegidos
            if (in_array($certName, $protected, true) || in_array($norm, $protected, true)) {
                continue;
            }

            // si no está en la lista de dominios conocidos → posible huérfano
            if (!in_array($certName, $known, true) && !in_array($norm, $knownNormalized, true)) {
                $this->warn("• ORPHAN CERT: {$certName}");

                // ¿existen vhosts asociados?
                $v80  = "/etc/apache2/sites-available/{$certName}.conf";
                $v443 = "/etc/apache2/sites-available/{$certName}-le-ssl.conf";

                $v80e  = file_exists($v80)  ? 'sí' : 'no';
                $v443e = file_exists($v443) ? 'sí' : 'no';

                $this->line("   - Apache :80 conf existe? {$v80e} | :443 conf existe? {$v443e}");

                // renewal conf (útil para ver “actividad”)
                $renew = "/etc/letsencrypt/renewal/{$certName}.conf";
                if (file_exists($renew)) {
                    $mtime = date('Y-m-d H:i:s', filemtime($renew));
                    $this->line("   - renewal conf: {$renew} (mtime {$mtime})");
                }
            }
        }

        $this->info('[SSL] Reporte de huérfanos finalizado (nada se eliminó).');
    }
}
