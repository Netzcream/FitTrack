<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\StudentPlanAssignment;
use App\Services\Tenant\InvoiceService;
use App\Enums\PlanAssignmentStatus;
use Carbon\Carbon;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate-monthly {--date=}';
    protected $description = 'Genera invoices mensuales al vencer el plan del alumno';

    public function handle(InvoiceService $invoiceService): int
    {
        $targetDate = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : now()->startOfDay();

        $this->info('Generando invoices mensuales...');

        $tenants = Tenant::where('status', 'active')->get();
        if ($tenants->isEmpty()) {
            $this->info('No hay tenants activos.');
            return self::SUCCESS;
        }

        $totalCreated = 0;
        $totalErrors = 0;
        foreach ($tenants as $tenant) {
            $tenant->run(function () use ($tenant, $invoiceService, $targetDate, &$totalCreated, &$totalErrors) {
                $created = 0;
                $errors = 0;

                $assignments = StudentPlanAssignment::query()
                    ->with('student')
                    ->whereNotNull('ends_at')
                    ->whereDate('ends_at', '<=', $targetDate)
                    ->whereIn('status', [
                        PlanAssignmentStatus::ACTIVE->value,
                        PlanAssignmentStatus::COMPLETED->value,
                    ])
                    ->get();

                foreach ($assignments as $assignment) {
                    $student = $assignment->student;
                    if (!$student) {
                        continue;
                    }

                    if (($student->billing_frequency ?? 'monthly') !== 'monthly') {
                        continue;
                    }

                    if (!$student->commercial_plan_id) {
                        continue;
                    }

                    if ($student->pendingInvoices()->exists()) {
                        continue;
                    }

                    if (Invoice::where('plan_assignment_id', $assignment->id)->exists()) {
                        continue;
                    }

                    $dueDate = $assignment->ends_at ? Carbon::parse($assignment->ends_at) : $targetDate;
                    if ($dueDate->lt($targetDate)) {
                        $dueDate = $targetDate;
                    }

                    try {
                        $invoiceService->createForStudent(
                            $student,
                            $assignment,
                            $dueDate,
                            null,
                            [
                                'source' => 'recurring',
                                'period_end' => $assignment->ends_at?->format('Y-m-d'),
                            ]
                        );
                        $created++;
                    } catch (\Throwable $e) {
                        $errors++;
                    }
                }

                if ($created > 0) {
                    $this->line("Tenant {$tenant->id}: {$created} invoices generadas");
                }
                if ($errors > 0) {
                    $this->line("Tenant {$tenant->id}: {$errors} errores");
                }

                $totalCreated += $created;
                $totalErrors += $errors;
            });
        }

        $this->info("Total: {$totalCreated} invoices generadas");
        if ($totalErrors > 0) {
            $this->warn("Errores: {$totalErrors}");
        }

        return self::SUCCESS;
    }
}
