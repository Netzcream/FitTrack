<?php

namespace App\Services\Tenant;

use App\Models\Tenant\{Invoice, Student, StudentPlanAssignment};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Crear invoice para un alumno basado en su plan comercial o un monto manual
     */
    public function createForStudent(
        Student $student,
        ?StudentPlanAssignment $planAssignment = null,
        ?Carbon $dueDate = null,
        ?float $amount = null,
        array $metaOverrides = []
    ): Invoice {
        return DB::transaction(function () use ($student, $planAssignment, $dueDate, $amount, $metaOverrides) {
            // Obtener el pricing del plan comercial
            $pricing = $this->resolvePlanPricing($student);

            $resolvedAmount = $amount ?? ($pricing['amount'] ?? null);
            if (empty($resolvedAmount) || $resolvedAmount <= 0) {
                throw new \RuntimeException('No hay un plan economico configurado para el alumno.');
            }

            // Obtener el nombre del plan - primero del pricing, si no del estudiante
            $planName = $pricing['plan_name'] ?? $student->commercialPlan?->name ?? null;

            $meta = array_filter([
                'plan_name' => $planName,
                'billing_frequency' => $pricing['type'] ?? $student->billing_frequency ?? 'monthly',
                'currency' => $pricing['currency'] ?? 'ARS',
                'label' => $pricing['label'] ?? null,
            ], fn($value) => $value !== null && $value !== '');

            $metaOverrides = array_filter($metaOverrides, fn($value) => $value !== null && $value !== '');
            $meta = array_merge($meta, $metaOverrides);

            return Invoice::create([
                'student_id' => $student->id,
                'plan_assignment_id' => $planAssignment?->id,
                'amount' => $resolvedAmount,
                'status' => 'pending',
                'due_date' => $dueDate ?? now()->addDays(7),
                'meta' => $meta ?: null,
            ]);
        });
    }

    /**
     * Marcar invoice como pagado
     */
    public function markAsPaid(
        Invoice $invoice,
        string $paymentMethod,
        ?string $externalReference = null,
        ?Carbon $paidAt = null
    ): Invoice {
        $invoice->update([
            'status' => 'paid',
            'paid_at' => $paidAt ?? now(),
            'payment_method' => $paymentMethod,
            'external_reference' => $externalReference ?? $invoice->external_reference,
        ]);

        return $invoice;
    }

    /**
     * Marcar invoice como vencido
     */
    public function markAsOverdue(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => 'overdue']);
        return $invoice;
    }

    /**
     * Cancelar invoice
     */
    public function cancel(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => 'cancelled']);
        return $invoice;
    }

    /**
     * Obtener invoices pendientes de un alumno
     */
    public function getPendingForStudent(Student $student)
    {
        return Invoice::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Obtener la proxima invoice pendiente de un alumno
     */
    public function getNextPendingForStudent(Student $student): ?Invoice
    {
        return Invoice::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->first();
    }

    /**
     * Verificar si un alumno tiene invoices vencidos
     */
    public function hasOverdueInvoices(Student $student): bool
    {
        return Invoice::where('student_id', $student->id)
            ->where('status', 'overdue')
            ->exists();
    }

    /**
     * Actualizar invoices vencidos
     */
    public function updateOverdueInvoices(): int
    {
        return Invoice::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);
    }

    /**
     * Resolver el pricing del plan comercial del alumno
     */
    private function resolvePlanPricing(Student $student): array
    {
        $plan = $student->commercialPlan;
        if (!$plan) {
            return [];
        }

        $pricing = collect($plan->pricing ?? []);
        if ($pricing->isEmpty()) {
            return [];
        }

        $billingFrequency = $student->billing_frequency ?? 'monthly';
        $selected = $pricing->firstWhere('type', $billingFrequency) ?? $pricing->first();

        if (!$selected) {
            return [];
        }

        return [
            'plan_name' => $plan->name,
            'type' => $selected['type'] ?? $billingFrequency,
            'amount' => isset($selected['amount']) ? (float) $selected['amount'] : null,
            'currency' => $selected['currency'] ?? 'ARS',
            'label' => $selected['label'] ?? null,
        ];
    }
}

