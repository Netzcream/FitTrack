<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Tenant\StudentPlanAssignment;
use App\Enums\PlanAssignmentStatus;
use Illuminate\Support\Facades\DB;

class ActivatePendingPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plans:activate-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activa los planes pendientes cuya fecha de inicio ya llegó (para todos los tenants)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Procesando planes pendientes para todos los tenants...');
        $this->newLine();

        $totalActivated = 0;
        $totalCancelled = 0;
        $tenantsProcessed = 0;

        // Obtener todos los tenants activos
        $tenants = Tenant::where('status', 'active')->get();

        if ($tenants->isEmpty()) {
            $this->info('No hay tenants activos.');
            return self::SUCCESS;
        }

        // Iterar cada tenant y procesar sus planes
        foreach ($tenants as $tenant) {
            $tenant->run(function () use ($tenant, &$totalActivated, &$totalCancelled, &$tenantsProcessed) {
                $activated = 0;
                $cancelled = 0;

                // Buscar planes pendientes cuya fecha de inicio ya llegó
                $pendingPlans = StudentPlanAssignment::query()
                    ->where('status', PlanAssignmentStatus::PENDING)
                    ->where('starts_at', '<=', now())
                    ->get();

                if ($pendingPlans->isEmpty()) {
                    return;
                }

                $this->line("<fg=cyan>Tenant: {$tenant->id}</>");

                foreach ($pendingPlans as $plan) {
                    DB::transaction(function () use ($plan, &$activated, &$cancelled) {
                        // Cancelar cualquier plan activo del mismo estudiante
                        $activePlans = StudentPlanAssignment::query()
                            ->where('student_id', $plan->student_id)
                            ->where('status', PlanAssignmentStatus::ACTIVE)
                            ->where('id', '!=', $plan->id)
                            ->get();

                        foreach ($activePlans as $activePlan) {
                            $activePlan->update([
                                'status' => PlanAssignmentStatus::CANCELLED,
                                'is_active' => false,
                                'ends_at' => now(),
                            ]);
                            $cancelled++;
                            $this->line("  <fg=red>✗</> Plan cancelado: {$activePlan->name} (ID: {$activePlan->id})");
                        }

                        // Activar el plan pendiente
                        $plan->update([
                            'status' => PlanAssignmentStatus::ACTIVE,
                            'is_active' => true,
                        ]);
                        $activated++;
                        $this->line("  <fg=green>✓</> Plan activado: {$plan->name} (ID: {$plan->id}) para estudiante ID: {$plan->student_id}");
                    });
                }

                $totalActivated += $activated;
                $totalCancelled += $cancelled;
                $tenantsProcessed++;

                if ($activated > 0 || $cancelled > 0) {
                    $this->line("  → Activados: {$activated}, Cancelados: {$cancelled}");
                    $this->newLine();
                }
            });
        }

        $this->info("✅ Proceso completado:");
        $this->info("   - Tenants procesados: {$tenantsProcessed}");
        $this->info("   - Total planes activados: {$totalActivated}");
        $this->info("   - Total planes cancelados: {$totalCancelled}");

        return self::SUCCESS;
    }
}
