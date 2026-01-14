<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant\Invoice;
use App\Services\Tenant\InvoiceService;

class MercadoPagoWebhookController extends Controller
{
    /**
     * Recibir notificaciones de Mercado Pago
     * POST /tenant/webhooks/mercadopago
     */
    public function handle(Request $request, InvoiceService $invoiceService)
    {
        // Log de la notificación recibida
        Log::info('MercadoPago webhook received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        // Mercado Pago envía el tipo de notificación
        $type = $request->input('type');
        $action = $request->input('action');

        // Solo procesar notificaciones de pago
        if ($type !== 'payment' && $action !== 'payment.created' && $action !== 'payment.updated') {
            Log::info('MercadoPago webhook ignored (not a payment notification)', [
                'type' => $type,
                'action' => $action,
            ]);
            return response()->json(['status' => 'ignored'], 200);
        }

        // Obtener el ID del pago
        $paymentId = $request->input('data.id');
        if (!$paymentId) {
            Log::warning('MercadoPago webhook without payment ID');
            return response()->json(['error' => 'payment_id missing'], 400);
        }

        try {
            // Consultar el estado del pago en Mercado Pago
            $mpPayment = $this->getPaymentFromMercadoPago($paymentId);

            if (!$mpPayment) {
                Log::error('Could not retrieve payment from MercadoPago', ['payment_id' => $paymentId]);
                return response()->json(['error' => 'payment not found'], 404);
            }

            // Obtener external_reference para encontrar el invoice
            $externalReference = $mpPayment['external_reference'] ?? null;

            if (!$externalReference || !str_starts_with($externalReference, 'INV-')) {
                Log::warning('MercadoPago payment without valid external_reference', [
                    'payment_id' => $paymentId,
                    'external_reference' => $externalReference,
                ]);
                return response()->json(['status' => 'ignored'], 200);
            }

            // Extraer ID del invoice
            $invoiceId = (int) str_replace('INV-', '', $externalReference);
            $invoice = Invoice::find($invoiceId);

            if (!$invoice) {
                Log::error('Invoice not found from external_reference', [
                    'external_reference' => $externalReference,
                    'invoice_id' => $invoiceId,
                ]);
                return response()->json(['error' => 'invoice not found'], 404);
            }

            // Actualizar estado del invoice según el estado del pago
            $status = $mpPayment['status'] ?? null;
            $statusDetail = $mpPayment['status_detail'] ?? null;

            Log::info('Processing MercadoPago payment status', [
                'invoice_id' => $invoice->id,
                'payment_id' => $paymentId,
                'status' => $status,
                'status_detail' => $statusDetail,
            ]);

            switch ($status) {
                case 'approved':
                    if ($invoice->status !== 'paid') {
                        $invoiceService->markAsPaid(
                            $invoice,
                            'mercadopago',
                            $paymentId
                        );
                        Log::info('Invoice marked as paid', ['invoice_id' => $invoice->id]);
                    }
                    break;

                case 'pending':
                case 'in_process':
                case 'in_mediation':
                    // Mantener como pendiente
                    if ($invoice->status !== 'paid') {
                        $invoice->update(['status' => 'pending']);
                    }
                    break;

                case 'rejected':
                case 'cancelled':
                case 'refunded':
                case 'charged_back':
                    // Si estaba pagado y fue rechazado/reembolsado, marcar como pendiente
                    if ($invoice->status === 'paid') {
                        $invoice->update([
                            'status' => 'pending',
                            'paid_at' => null,
                        ]);
                        Log::warning('Invoice reverted from paid to pending', [
                            'invoice_id' => $invoice->id,
                            'reason' => $status,
                        ]);
                    }
                    break;
            }

            return response()->json(['status' => 'processed'], 200);

        } catch (\Throwable $e) {
            Log::error('Error processing MercadoPago webhook', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'internal error'], 500);
        }
    }

    /**
     * Consultar el pago en Mercado Pago
     */
    private function getPaymentFromMercadoPago(string $paymentId): ?array
    {
        try {
            $token = tenant_config('payment_mp_access_token');
            if (!$token) {
                return null;
            }

            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.mercadopago.com/v1/payments/{$paymentId}", [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(), true);
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Error fetching payment from MercadoPago', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
