<?php

namespace App\Livewire\Tenant\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Tenant\{Invoice, Payment, Student};
use App\Services\Tenant\{InvoiceService, Payments\MercadoPagoService};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use MercadoPago\Exceptions\MPApiException;

#[Layout('layouts.student')]
class Payments extends Component
{
    public ?string $paymentError = null;
    public ?Invoice $pendingInvoice = null;

    public function mount()
    {
        // Cargar invoice pendiente si existe
        $user = Auth::user();
        if ($user && $user->student) {
            $invoiceService = new InvoiceService();
            $this->pendingInvoice = $invoiceService->getNextPendingForStudent($user->student);

            // Detectar si venimos de Mercado Pago (back_url redirect)
            // Mercado Pago envía: ?payment_id=...&status=approved&external_reference=INV-{id}&merchant_order_id=...&preference_id=...
            if (request()->has('external_reference')) {
                $this->processMercadoPagoReturn($invoiceService);

                // Limpiar los parámetros y redirigir limpio
                return redirect()->route('tenant.student.payments');
            }
        }
    }

    /**
     * Procesar el retorno automático de Mercado Pago
     */
    private function processMercadoPagoReturn(InvoiceService $invoiceService): void
    {
        $externalReference = request()->query('external_reference');
        $paymentId = request()->query('payment_id');
        $status = request()->query('status');

        Log::info('Mercado Pago return detected', [
            'external_reference' => $externalReference,
            'payment_id' => $paymentId,
            'status' => $status,
            'all_params' => request()->query(),
        ]);

        // Extraer invoice ID del external_reference (formato: INV-{id})
        if (!$externalReference || !str_starts_with($externalReference, 'INV-')) {
            return;
        }

        $invoiceId = (int) str_replace('INV-', '', $externalReference);
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            return;
        }

        // Procesar según el status
        if ($status === 'approved' || $status === 'paid') {
            $invoiceService->markAsPaid($invoice, 'mercadopago', $paymentId);
            session()->flash('success', '¡Pago realizado exitosamente! Tu plan está activo.');
        } elseif ($status === 'pending') {
            session()->flash('warning', 'Tu pago está en proceso de confirmación. Te notificaremos cuando se complete.');
        } else {
            session()->flash('error', 'El pago no se pudo procesar. Intenta de nuevo.');
        }

        // Recargar el invoice pendiente después de procesar
        $this->pendingInvoice = $invoiceService->getNextPendingForStudent(Auth::user()->student);
    }

    private function resolvePlanPricing(?Student $student): array
    {
        if (!$student) {
            return [];
        }

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

    public function payWithMercadoPago(): void
    {
        $this->paymentError = null;

        /** @var \App\Models\User|null $user */
        $user =  Auth::user();
        if (!$user || !$user->student) {
            abort(403);
        }

        $student = $user->student->load('commercialPlan');

        $mpConfig = payment_method_config('mercadopago');
        if (empty($mpConfig['enabled']) || empty($mpConfig['access_token'])) {
            $this->paymentError = 'Mercado Pago no esta configurado para este gimnasio.';
            return;
        }

        try {
            $invoiceService = new InvoiceService();

            // Buscar invoice pendiente o crear uno nuevo
            $invoice = $invoiceService->getNextPendingForStudent($student);

            if (!$invoice) {
                $invoice = $invoiceService->createForStudent($student, $student->currentPlanAssignment);
            }

            $mp = new MercadoPagoService();
            $url = $mp->createInvoicePaymentLink($invoice);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            Log::error('MercadoPago API error creating invoice payment link', [
                'invoice_id' => $invoice->id ?? null,
                'student_id' => $student->id,
                'status' => $e->getStatusCode(),
                'response' => $apiResponse ? $apiResponse->getContent() : null,
            ]);
            $this->paymentError = 'No se pudo iniciar el pago. Intenta de nuevo en unos minutos.';
            return;
        } catch (\Throwable $e) {
            Log::error('MercadoPago error creating invoice payment link', [
                'invoice_id' => $invoice->id ?? null,
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);
            $this->paymentError = 'No se pudo iniciar el pago. Intenta de nuevo en unos minutos.';
            return;
        }

        $this->pendingInvoice = $invoice;
        // Redirigir directamente a Mercado Pago con HTTPS en back_urls
        $this->redirect($url, navigate: false);
    }

    public function render()
    {
        $acceptedMethods = accepted_payment_methods();
        $transferConfig = payment_method_config('transfer');
        $mercadopagoConfig = payment_method_config('mercadopago');
        $cashConfig = payment_method_config('cash');

        /** @var User $user */
        $user = Auth::user();
        $student = $user->student?->load(['commercialPlan', 'currentPlanAssignment']);

        $pricing = $this->resolvePlanPricing($student);

        // Obtener invoice pendiente si existe
        if ($student) {
            $invoiceService = new InvoiceService();
            $this->pendingInvoice = $invoiceService->getNextPendingForStudent($student);
        }

        // Permitir pago por Mercado Pago SOLO si hay un invoice pendiente
        $canPayMercadopago = (
            $this->pendingInvoice
        ) && ($mercadopagoConfig['enabled'] ?? false)
            && !empty($mercadopagoConfig['access_token']);

        // Obtener historial de invoices del estudiante (últimos 10)
        if ($student) {
            $invoices = Invoice::where('student_id', $student->id)
                ->orderByDesc('created_at')
                ->take(10)
                ->with('planAssignment.plan')
                ->get()
                ->map(function ($invoice) {
                    $invoice->has_plan_assignment = !empty($invoice->plan_assignment_id);
                    return $invoice;
                });
            $hasMoreInvoices = Invoice::where('student_id', $student->id)->count() > 10;
        } else {
            $invoices = collect();
            $hasMoreInvoices = false;
        }

        return view('livewire.tenant.student.payments', [
            'acceptedMethods' => $acceptedMethods,
            'transferConfig' => $transferConfig,
            'mercadopagoConfig' => $mercadopagoConfig,
            'cashConfig' => $cashConfig,
            'student' => $student,
            'pricing' => $pricing,
            'canPayMercadopago' => $canPayMercadopago,
            'pendingInvoice' => $this->pendingInvoice,
            'invoices' => $invoices,
            'hasMoreInvoices' => $hasMoreInvoices,
        ]);
    }
}
