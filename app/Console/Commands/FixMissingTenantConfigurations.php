<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\TenantConfiguration;

class FixMissingTenantConfigurations extends Command
{
    protected $signature = 'tenants:fix-configurations';
    protected $description = 'Crea configuraciones faltantes para tenants existentes';

    public function handle(): int
    {
        $tenants = Tenant::all();
        $created = 0;

        foreach ($tenants as $tenant) {
            $exists = TenantConfiguration::where('tenant_id', $tenant->id)->exists();

            if (! $exists) {
                TenantConfiguration::create([
                    'tenant_id' => $tenant->id,
                    'data' => [],
                ]);
                $created++;
            }
        }

        $this->info("Configuraciones creadas: $created");

        return 0;
    }
}
