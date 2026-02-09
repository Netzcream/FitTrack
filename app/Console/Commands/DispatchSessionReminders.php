<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\SendSessionRemindersForTenant;
use App\Models\Tenant;
use Illuminate\Console\Command;

class DispatchSessionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:session-reminders {--tenant=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Despacha jobs de recordatorios de sesion para tenants activos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantOption = $this->option('tenant');
        $force = (bool) $this->option('force');

        $query = Tenant::query()->where('status', 'active');

        if (is_string($tenantOption) && trim($tenantOption) !== '') {
            $query->where('id', trim($tenantOption));
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants activos para procesar.');
            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            SendSessionRemindersForTenant::dispatch((string) $tenant->id, $force);
            $this->line("Job encolado para tenant {$tenant->id}");
        }

        $this->info('Recordatorios encolados: ' . $tenants->count());

        return self::SUCCESS;
    }
}
