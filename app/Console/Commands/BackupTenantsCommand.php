<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupTenantsCommand extends Command
{
    protected $signature = 'app:backup-tenants
                            {--force : Forzar backup aunque ya exista uno del día}
                            {--keep-days=7 : Número de días de backups diarios a mantener}
                            {--keep-weekly=30 : Número de días de backups semanales (domingos) a mantener}
                            {--tenant= : Backup solo de un tenant específico}';
    protected $description = 'Genera backups de todas las bases de datos de tenants y limpia los viejos';

    public function handle(): int
    {
        $basePath = storage_path('app/backups');
        $today = now()->format('Y-m-d');

        File::ensureDirectoryExists($basePath);
        $this->info('🔄 Iniciando backups de tenants...');

        $tenants = $this->option('tenant')
            ? Tenant::where('id', $this->option('tenant'))->get()
            : Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn('⚠️  No se encontraron tenants para respaldar');
            return self::SUCCESS;
        }

        $this->info("📊 Total de tenants a respaldar: {$tenants->count()}");
        $totalSize = 0;
        $successCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($tenants as $tenant) {
            $result = $this->backupTenant($tenant, $basePath, $today);

            if ($result['status'] === 'success') {
                $successCount++;
                $totalSize += $result['size'];
            } elseif ($result['status'] === 'skipped') {
                $skippedCount++;
            } else {
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info('📋 Resumen del proceso:');
        $this->line("  ✅ Exitosos: {$successCount}");
        if ($skippedCount > 0) {
            $this->line("  ⏭️  Omitidos (ya existían): {$skippedCount}");
        }
        if ($errorCount > 0) {
            $this->line("  ❌ Errores: {$errorCount}");
        }
        $this->line("  📦 Tamaño total: " . $this->formatBytes($totalSize));

        return $errorCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function backupTenant(Tenant $tenant, string $basePath, string $today): array
    {
        $database = $this->resolveTenantDatabaseName((string) $tenant->id);
        $tenantDir = "{$basePath}/{$tenant->id}";
        File::ensureDirectoryExists($tenantDir);

        $existingBackup = $this->findTodayBackup($tenantDir, $today);
        if ($existingBackup && ! $this->option('force')) {
            $this->line("  ⏭️  {$tenant->id} - Ya existe backup del día: {$existingBackup}");
            return ['status' => 'skipped', 'size' => 0];
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $file = "{$tenantDir}/{$timestamp}.sql.gz";

        $this->line("  🔄 {$tenant->id} → respaldando...");

        $credFile = $this->createMysqlCredentialsFile();

        try {
            $dumpBin = env('MYSQLDUMP_BIN', 'mysqldump');

            $cmd = sprintf(
                '%s --defaults-extra-file=%s --host=%s --single-transaction --quick --lock-tables=false %s 2>&1 | gzip > %s',
                escapeshellcmd($dumpBin),
                escapeshellarg($credFile),
                escapeshellarg(env('DB_HOST', '127.0.0.1')),
                escapeshellarg($database),
                escapeshellarg($file)
            );

            exec($cmd, $output, $exitCode);

            if (file_exists($credFile)) {
                unlink($credFile);
            }

            if ($exitCode !== 0) {
                $this->error("    ❌ Error al respaldar {$tenant->id}");
                if (! empty($output)) {
                    $this->line("       " . implode("\n       ", $output));
                }
                return ['status' => 'error', 'size' => 0];
            }

            if (! file_exists($file) || filesize($file) === 0) {
                $this->error('    ❌ El archivo de backup está vacío o no se creó');
                if (file_exists($file)) {
                    unlink($file);
                }
                return ['status' => 'error', 'size' => 0];
            }

            $size = filesize($file);
            $this->line("    ✅ {$tenant->id} - " . $this->formatBytes($size));

            $this->cleanupOldBackups($tenantDir);

            return ['status' => 'success', 'size' => $size];
        } catch (\Exception $e) {
            if (file_exists($credFile)) {
                unlink($credFile);
            }
            $this->error("    ❌ Excepción: {$e->getMessage()}");
            return ['status' => 'error', 'size' => 0];
        }
    }

    protected function resolveTenantDatabaseName(string $tenantId): string
    {
        $prefix = (string) config('tenancy.database.prefix', '');
        $suffix = (string) config('tenancy.database.suffix', '');

        return $prefix . $tenantId . $suffix;
    }

    protected function createMysqlCredentialsFile(): string
    {
        $credFile = sys_get_temp_dir() . '/mysql_credentials_' . uniqid() . '.cnf';

        $content = sprintf(
            "[client]\nuser=%s\npassword=%s\n",
            env('DB_USERNAME'),
            env('DB_PASSWORD')
        );

        file_put_contents($credFile, $content);
        chmod($credFile, 0600);

        return $credFile;
    }

    protected function findTodayBackup(string $tenantDir, string $today): ?string
    {
        if (! is_dir($tenantDir)) {
            return null;
        }

        $files = glob($tenantDir . '/' . $today . '_*.sql.gz');

        return ! empty($files) ? basename($files[0]) : null;
    }

    protected function cleanupOldBackups(string $tenantDir): void
    {
        $keepDays = (int) $this->option('keep-days');
        $keepWeekly = (int) $this->option('keep-weekly');

        $files = collect(File::files($tenantDir))
            ->filter(fn($f) => str_ends_with($f->getFilename(), '.sql.gz'))
            ->sortBy(fn($f) => $f->getCTime());

        $deletedCount = 0;
        $deletedSize = 0;

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $raw = substr($filename, 0, 10);

            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
                continue;
            }

            $date = Carbon::createFromFormat('Y-m-d', $raw);
            $age = $date->diffInDays(now());
            $dow = $date->dayOfWeek;

            if ($age <= $keepDays) {
                continue;
            }

            if ($age <= $keepWeekly && $dow === 0) {
                continue;
            }

            $size = $file->getSize();
            if (File::delete($file->getRealPath())) {
                $deletedCount++;
                $deletedSize += $size;
            }
        }

        if ($deletedCount > 0) {
            $this->line("    🧹 Eliminados {$deletedCount} backups antiguos (" . $this->formatBytes($deletedSize) . ')');
        }
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
