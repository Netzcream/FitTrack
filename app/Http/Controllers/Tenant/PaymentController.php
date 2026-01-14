<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant\{Invoice, Payment, Student};
use App\Services\Tenant\{InvoiceService, Payments\MercadoPagoService};

class PaymentController extends Controller
{
    /**
     * Crear invoice y generar link de pago con Mercado Pago
     */
    public function createInvoice(Request $request, InvoiceService $invoiceService, MercadoPagoService $mp)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($data['student_id']);

        // Crear o usar invoice pendiente
        $invoice = $invoiceService->getNextPendingForStudent($student);

        if (!$invoice) {
            $invoice = $invoiceService->createForStudent($student);
        }

        $url = $mp->createInvoicePaymentLink($invoice);

        return response()->json([
            'url' => $url,
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount,
        ]);
    }

    /**
     * Legacy: Crear payment con Mercado Pago
     * @deprecated Usar createInvoice en su lugar
     */
    public function create(Request $request, MercadoPagoService $mp)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount'     => 'required|numeric|min:0.1',
        ]);

        $student = Student::findOrFail($data['student_id']);

        $payment = Payment::create([
            'student_id' => $student->id,
            'amount'     => $data['amount'],
            'method'     => 'mercadopago',
            'status'     => 'pending',
        ]);

        $url = $mp->createPaymentLink($payment);

        return response()->json(['url' => $url]);
    }

    /**
     * Verificar el estado del pago cuando el usuario retorna de Mercado Pago
     * POST /student/payments/verify-mercadopago
     */
    public function verifyMercadoPago(Request $request, InvoiceService $invoiceService)
    {
        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        $invoice = Invoice::findOrFail($data['invoice_id']);

        // Obtener el preference_id desde el external_reference
        $preferenceId = $invoice->external_reference;

        if (!$preferenceId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No preference encontrado'
            ], 400);
        }

        try {
            // Consultar el estado de la preferencia en Mercado Pago
            $paymentStatus = $this->checkMercadoPagoPayment($preferenceId);

            if ($paymentStatus === 'approved') {
                // Marcar como pagado
                $invoiceService->markAsPaid($invoice, 'mercadopago', $preferenceId);

                return response()->json([
                    'status' => 'paid',
                    'message' => 'Pago verificado y procesado'
                ]);
            } elseif ($paymentStatus === 'pending') {
                return response()->json([
                    'status' => 'pending',
                    'message' => 'Pago aún en proceso'
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'El pago no fue aprobado'
                ], 400);
            }
        } catch (\Throwable $e) {
            Log::error('Error verifying Mercado Pago payment', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al verificar el pago'
            ], 500);
        }
    }

    /**
     * Consultar el estado de un pago en Mercado Pago usando la preference_id
     */
    private function checkMercadoPagoPayment(string $preferenceId): ?string
    {
        try {
            $token = tenant_config('payment_mp_access_token');
            if (!$token) {
                return null;
            }

            $client = new \GuzzleHttp\Client();

            // Obtener los detalles de la preferencia
            $response = $client->get("https://api.mercadopago.com/v1/checkout/preferences/{$preferenceId}", [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);

                // Si hay pagos asociados a esta preferencia
                if (!empty($data['payments'])) {
                    // Obtener el pago más reciente
                    $payment = end($data['payments']);
                    return $payment['status'] ?? null;
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Error fetching Mercado Pago preference', [
                'preference_id' => $preferenceId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Procesar la respuesta GET de Mercado Pago
     * GET /student/payments?payment_id=...&status=...&external_reference=...
     */
    public function handleMercadopagoReturn(Request $request, InvoiceService $invoiceService)
    {
        // Mercado Pago envía estos parámetros:
        // payment_id, status, external_reference, merchant_order_id, preference_id, etc.

        $paymentId = $request->query('payment_id');
        $status = $request->query('status');
        $externalReference = $request->query('external_reference');

        Log::info('Mercado Pago return received', [
            'payment_id' => $paymentId,
            'status' => $status,
            'external_reference' => $externalReference,
            'all_params' => $request->query(),
        ]);

        // Extraer el ID del invoice desde external_reference (formato: INV-{id})
        $invoiceId = null;
        if ($externalReference && str_starts_with($externalReference, 'INV-')) {
            $invoiceId = (int) str_replace('INV-', '', $externalReference);
        }

        if (!$invoiceId) {
            Log::warning('No invoice ID found in external reference', [
                'external_reference' => $externalReference,
            ]);
            return redirect()->route('tenant.student.payments')
                ->with('error', 'No se pudo procesar el pago: referencia inválida.');
        }

        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            Log::warning('Invoice not found', ['invoice_id' => $invoiceId]);
            return redirect()->route('tenant.student.payments')
                ->with('error', 'No se encontró el pago asociado.');
        }

        // Procesar según el estado del pago
        if ($status === 'approved' || $status === 'paid') {
            // Pago aprobado
            $invoiceService->markAsPaid($invoice, 'mercadopago', $paymentId);

            Log::info('Payment approved and marked as paid', [
                'invoice_id' => $invoiceId,
                'payment_id' => $paymentId,
            ]);

            return redirect()->route('tenant.student.payments')
                ->with('success', '¡Pago realizado exitosamente! Tu plan está activo.');
        } elseif ($status === 'pending') {
            // Pago pendiente (ej: transferencia bancaria)
            Log::info('Payment pending', [
                'invoice_id' => $invoiceId,
                'payment_id' => $paymentId,
            ]);

            return redirect()->route('tenant.student.payments')
                ->with('warning', 'Tu pago está en proceso. Te notificaremos cuando se confirme.');
        } else {
            // Pago rechazado o error
            Log::warning('Payment rejected or failed', [
                'invoice_id' => $invoiceId,
                'payment_id' => $paymentId,
                'status' => $status,
            ]);

            return redirect()->route('tenant.student.payments')
                ->with('error', 'El pago fue rechazado. Por favor intenta nuevamente.');
        }
    }
