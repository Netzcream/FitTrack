<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Tenant\StudentPlanAssignment;
use App\Enums\PlanAssignmentStatus;

class DeactivateExpiredPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plans:deactivate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marca como completados los planes cuya fecha de finalización ya pasó (para todos los tenants)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Procesando planes vencidos para todos los tenants...');
        $this->newLine();

        $totalCompleted = 0;
        $tenantsProcessed = 0;

        // Obtener todos los tenants activos
        $tenants = Tenant::where('status', 'active')->get();

        if ($tenants->isEmpty()) {
            $this->info('No hay tenants activos.');
            return self::SUCCESS;
        }

        // Iterar cada tenant y procesar sus planes
        foreach ($tenants as $tenant) {
            $tenant->run(function () use ($tenant, &$totalCompleted, &$tenantsProcessed) {
                $count = 0;

                // Buscar planes activos cuya fecha de finalización ya pasó
                $expiredPlans = StudentPlanAssignment::query()
                    ->where('status', PlanAssignmentStatus::ACTIVE)
                    ->whereNotNull('ends_at')
                    ->where('ends_at', '<', now())
                    ->get();

                if ($expiredPlans->isEmpty()) {
                    return;
                }

                $this->line("<fg=cyan>Tenant: {$tenant->id}</>");

                foreach ($expiredPlans as $plan) {
                    $plan->update([
                        'status' => PlanAssignmentStatus::COMPLETED,
                        'is_active' => false,
                    ]);
                    $count++;
                    $this->line("  <fg=blue>✓</> Plan completado: {$plan->name} (ID: {$plan->id}) - Vencido el {$plan->ends_at->format('d/m/Y')}");
                }

                $totalCompleted += $count;
                $tenantsProcessed++;

                $this->line("  → Completados: {$count}");
                $this->newLine();
            });
        }

        $this->info("✅ Proceso completado:");
        $this->info("   - Tenants procesados: {$tenantsProcessed}");
        $this->info("   - Total planes completados: {$totalCompleted}");

        return self::SUCCESS;
    }
}
