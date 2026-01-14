<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Student;
use App\Models\Tenant\Invoice;
use Illuminate\Support\Collection;

class StudentPaymentsDashboardService
{
    /**
     * Obtener todos los datos de pagos en una sola consulta
     */
    public function getPaymentsDashboardData(Student $student): array
    {
        return [
            'student' => $this->getStudentData($student),
            'pending_invoice' => $this->getPendingInvoice($student),
            'payment_methods' => $this->getAvailablePaymentMethods(),
            'payment_info' => $this->getPaymentInfo($student),
            'invoices_history' => $this->getInvoicesHistory($student),
            'commercial_plan' => $this->getCommercialPlanInfo($student),
        ];
    }

    /**
     * Datos básicos del estudiante
     */
    private function getStudentData(Student $student): array
    {
        return [
            'id' => $student->uuid,
            'name' => $student->name,
            'email' => $student->email,
            'phone' => $student->phone,
        ];
    }

    /**
     * Invoice pendiente (si existe)
     */
    private function getPendingInvoice(Student $student): ?array
    {
        $invoiceService = new InvoiceService();
        $invoice = $invoiceService->getNextPendingForStudent($student);

        if (!$invoice) {
            return null;
        }

        return [
            'id' => $invoice->id,
            'number' => $invoice->invoice_number,
            'amount' => (float) $invoice->amount,
            'currency' => $invoice->currency ?? 'ARS',
            'due_date' => $invoice->due_date?->toIso8601String(),
            'created_at' => $invoice->created_at?->toIso8601String(),
            'status' => $invoice->status,
            'description' => $invoice->description,
            'plan_assignment' => $invoice->plan_assignment_id ? [
                'uuid' => $invoice->planAssignment?->uuid,
                'name' => $invoice->planAssignment?->plan?->name ?? $invoice->planAssignment?->name,
            ] : null,
        ];
    }

    /**
     * Métodos de pago disponibles
     */
    private function getAvailablePaymentMethods(): array
    {
        $accepted = accepted_payment_methods();
        $methods = [];

        foreach ($accepted as $method) {
            $config = payment_method_config($method);
            $enabled = $config['enabled'] ?? false;

            if ($method === 'mercadopago') {
                $methods[] = [
                    'id' => 'mercadopago',
                    'name' => 'Mercado Pago',
                    'enabled' => $enabled && !empty($config['access_token']),
                    'logo' => 'mercadopago',
                    'description' => 'Transferencia bancaria, tarjeta de crédito, efectivo',
                ];
            } elseif ($method === 'transfer') {
                $methods[] = [
                    'id' => 'transfer',
                    'name' => 'Transferencia Bancaria',
                    'enabled' => $enabled,
                    'logo' => 'bank',
                    'description' => $config['account_holder'] ?? 'Transferencia directa a cuenta bancaria',
                    'account_details' => $this->getTransferDetails($config),
                ];
            } elseif ($method === 'cash') {
                $methods[] = [
                    'id' => 'cash',
                    'name' => 'Efectivo',
                    'enabled' => $enabled,
                    'logo' => 'cash',
                    'description' => 'Pago en efectivo en el lugar',
                ];
            }
        }

        return $methods;
    }

    /**
     * Detalles de transferencia bancaria
     */
    private function getTransferDetails(array $config): ?array
    {
        if (empty($config['account_holder'])) {
            return null;
        }

        return [
            'account_holder' => $config['account_holder'] ?? null,
            'bank_name' => $config['bank_name'] ?? null,
            'cbu' => $config['cbu'] ?? null,
            'alias' => $config['alias'] ?? null,
            'cuit_cuil' => $config['cuit_cuil'] ?? null,
            'instructions' => $config['instructions'] ?? null,
        ];
    }

    /**
     * Información de pago (métodos, moneda, etc.)
     */
    private function getPaymentInfo(Student $student): array
    {
        $commercialPlan = $student->commercialPlan;
        $pricing = $this->resolvePlanPricing($student);

        return [
            'plan_name' => $pricing['plan_name'] ?? 'Sin plan comercial',
            'billing_frequency' => $student->billing_frequency ?? 'monthly',
            'amount' => $pricing['amount'] ?? 0,
            'currency' => $pricing['currency'] ?? 'ARS',
            'pricing_type' => $pricing['type'] ?? null,
            'pricing_label' => $pricing['label'] ?? null,
        ];
    }

    /**
     * Resolver el precio del plan según frecuencia de facturación
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

    /**
     * Historial de invoices (últimos 10 + estadísticas)
     */
    private function getInvoicesHistory(Student $student): array
    {
        $invoices = Invoice::where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->with('planAssignment.plan')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'number' => $invoice->invoice_number,
                    'amount' => (float) $invoice->amount,
                    'currency' => $invoice->currency ?? 'ARS',
                    'status' => $invoice->status,
                    'created_at' => $invoice->created_at->toIso8601String(),
                    'due_date' => $invoice->due_date?->toIso8601String(),
                    'paid_at' => $invoice->paid_at?->toIso8601String(),
                    'description' => $invoice->description,
                    'plan_name' => $invoice->planAssignment?->plan?->name ?? $invoice->planAssignment?->name,
                ];
            });

        $totalInvoices = Invoice::where('student_id', $student->id)->count();

        return [
            'invoices' => $invoices->toArray(),
            'total_count' => $totalInvoices,
            'has_more' => $totalInvoices > 10,
            'statistics' => $this->getInvoiceStatistics($student),
        ];
    }

    /**
     * Estadísticas de invoices
     */
    private function getInvoiceStatistics(Student $student): array
    {
        $allInvoices = Invoice::where('student_id', $student->id)->get();

        $totalPaid = $allInvoices->where('status', 'paid')->sum('amount');
        $totalPending = $allInvoices->where('status', 'pending')->sum('amount');
        $totalCount = $allInvoices->count();
        $paidCount = $allInvoices->where('status', 'paid')->count();
        $pendingCount = $allInvoices->where('status', 'pending')->count();

        return [
            'total_paid' => (float) $totalPaid,
            'total_pending' => (float) $totalPending,
            'total_invoices' => $totalCount,
            'paid_count' => $paidCount,
            'pending_count' => $pendingCount,
            'payment_rate' => $totalCount > 0 ? round(($paidCount / $totalCount) * 100) : 0,
        ];
    }

    /**
     * Información del plan comercial
     */
    private function getCommercialPlanInfo(Student $student): ?array
    {
        $plan = $student->commercialPlan;

        if (!$plan) {
            return null;
        }

        return [
            'name' => $plan->name,
            'description' => $plan->description,
            'pricing' => $plan->pricing ?? [],
        ];
    }
}
