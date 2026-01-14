<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Tenant\InvoiceService;
use App\Models\Tenant;

class UpdateOverdueInvoices extends Command
{
    protected $signature = 'invoices:update-overdue';
    protected $description = 'Actualizar invoices vencidos a estado overdue';

    public function handle(InvoiceService $invoiceService): int
    {
        $this->info('Actualizando invoices vencidos...');

        $tenants = Tenant::where('status', 'active')->get();
        $totalUpdated = 0;

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            $updated = $invoiceService->updateOverdueInvoices();
            $totalUpdated += $updated;

            if ($updated > 0) {
                $this->line("Tenant {$tenant->id}: {$updated} invoices actualizados");
            }

            tenancy()->end();
        }

        $this->info("Total: {$totalUpdated} invoices actualizados a overdue");

        return self::SUCCESS;
    }
}
