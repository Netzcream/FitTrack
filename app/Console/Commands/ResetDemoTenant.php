<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetDemoTenant extends Command
{
    protected $signature = 'demo:reset-tenant {tenant? : Tenant ID a resetear}';

    protected $description = 'Resetea la base de datos del tenant demo y vuelve a ejecutar migraciones y seeds.';

    public function handle(): int
    {
        if (! config('demo.enabled')) {
            $this->warn('DEMO_MODE está deshabilitado. No se ejecuta el reset.');
            return self::SUCCESS;
        }

        $tenantId = (string) ($this->argument('tenant') ?: config('demo.tenant_id', 'demo'));
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            $this->error("No se encontró el tenant demo [{$tenantId}].");
            return self::FAILURE;
        }

        $this->info("Reseteando tenant demo [{$tenantId}]...");

        $exitCode = $tenant->run(function () use ($tenantId) {
            $migrateCode = Artisan::call('migrate:fresh', [
                '--database' => 'tenant',
                '--path' => database_path('migrations/tenant'),
                '--realpath' => true,
                '--force' => true,
            ]);

            $this->output->write(Artisan::output());

            if ($migrateCode !== 0) {
                return $migrateCode;
            }

            $seedCode = Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--class' => 'TenantSeeder',
                '--force' => true,
            ]);

            $this->output->write(Artisan::output());

            if ($seedCode !== 0) {
                return $seedCode;
            }

            $optimizeCode = Artisan::call('optimize:clear');
            $this->output->write(Artisan::output());

            $this->info("Tenant demo [{$tenantId}] reseteado correctamente.");

            return $optimizeCode;
        });

        return (int) $exitCode;
    }
}
