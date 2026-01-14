<?php

namespace App\Services\Tenant\Payments;

use App\Models\Tenant\{Invoice, Payment};
use Illuminate\Support\Facades\Log;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

class MercadoPagoService
{
    public function __construct()
    {
        $token = tenant_config('payment_mp_access_token');
        if (!$token) {
            throw new \RuntimeException('MercadoPago Access Token no configurado para este tenant');
        }

        MercadoPagoConfig::setAccessToken($token);
        // Siempre usar sandbox para desarrollo/universitario
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
    }

    /**
     * Crear link de pago para un Invoice
     */
    public function createInvoicePaymentLink(Invoice $invoice): string
    {
        $client = new PreferenceClient();

        // Obtener la URL actual del request (dominio del tenant)
        // IMPORTANTE: Mercado Pago requiere HTTPS en las back_urls
        $host = request()->getHost();
        $backUrl = 'https://' . $host . '/student/payments';

        $meta = $invoice->meta ?? [];
        $title = $meta['label'] ?? ($meta['plan_name'] ?? 'Abono');

        $payload = [
            'items' => [[
                'title' => $title,
                'quantity' => 1,
                'unit_price' => (float) $invoice->amount,
                'currency_id' => $meta['currency'] ?? 'ARS',
            ]],
            'external_reference' => 'INV-' . $invoice->id,
            // Mercado Pago redirige automáticamente aquí después del pago
            'back_urls' => [
                'success' => $backUrl,
                'failure' => $backUrl,
                'pending' => $backUrl,
            ],
            // Webhook: Mercado Pago nos notificará cuando se completa el pago
            'notification_url' => request()->getSchemeAndHttpHost() . '/webhooks/mercadopago',
        ];

        $student = $invoice->student;
        $payerEmail = '';
        if ($student) {
            $payerEmail = (string) ($student->email ?: ($student->user?->email ?? ''));
            $payerEmail = trim($payerEmail);
        }
        if ($payerEmail !== '') {
            $payload['payer'] = [
                'email' => $payerEmail,
            ];
        }

        Log::info('MercadoPago preference payload', [
            'invoice_id' => $invoice->id,
            'student_email' => $payerEmail,
            'back_urls' => $payload['back_urls'] ?? null,
            'has_back_urls' => isset($payload['back_urls']),
            'amount' => $payload['items'][0]['unit_price'] ?? null,
            'currency' => $payload['items'][0]['currency_id'] ?? null,
            'token_prefix' => substr(tenant_config('payment_mp_access_token'), 0, 20) . '...',
            'full_payload' => json_encode($payload, JSON_PRETTY_PRINT),
        ]);

        try {
            $preference = $client->create($payload);
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            Log::error('MercadoPago API Exception', [
                'invoice_id' => $invoice->id,
                'status' => $e->getStatusCode(),
                'message' => $e->getMessage(),
                'api_response' => $e->getApiResponse()?->getContent(),
                'payload' => $payload,
            ]);
            throw $e;
        }

        $invoice->update([
            'payment_method' => 'mercadopago',
            'external_reference' => $preference->id,
        ]);

        return $preference->init_point;
    }

    /**
     * Crear link de pago para un Payment (legacy)
     * @deprecated Usar createInvoicePaymentLink en su lugar
     */
    public function createPaymentLink(Payment $payment): string
    {
        $client = new PreferenceClient();

        // Obtener la URL actual del request (dominio del tenant)
        $backUrl = request()->getSchemeAndHttpHost() . '/student/payments';

        // Validar que sea una URL válida
        if (!filter_var($backUrl, FILTER_VALIDATE_URL)) {
            Log::warning('MercadoPago back URL invalid.', [
                'payment_id' => $payment->id,
                'back_url' => $backUrl,
            ]);
            $backUrl = '';
        }

        $hasBackUrl = filter_var($backUrl, FILTER_VALIDATE_URL) !== false;

        $payload = [
            'items' => [[
                'title' => 'Abono mensual',
                'quantity' => 1,
                'unit_price' => (float) $payment->amount,
                'currency_id' => 'ARS',
            ]],
            'external_reference' => 'PAY-' . $payment->id,
        ];

        if ($hasBackUrl) {
            // Back URL: permite que el usuario vuelva a la app después del pago
            $payload['back_urls'] = [
                'success' => $backUrl,
                'failure' => $backUrl,
                'pending' => $backUrl,
            ];
        } else {
            Log::warning('MercadoPago back URLs not configured.', [
                'payment_id' => $payment->id,
            ]);
        }

        $student = $payment->student;
        $payerEmail = '';
        if ($student) {
            $payerEmail = (string) ($student->email ?: ($student->user?->email ?? ''));
            $payerEmail = trim($payerEmail);
        }
        if ($payerEmail !== '') {
            $payload['payer'] = [
                'email' => $payerEmail,
            ];
        }

        Log::info('MercadoPago preference payload', [
            'payment_id' => $payment->id,
            'back_urls' => $payload['back_urls'] ?? null,
            'amount' => $payload['items'][0]['unit_price'] ?? null,
            'currency' => $payload['items'][0]['currency_id'] ?? null,
        ]);

        $preference = $client->create($payload);

        $payment->update([
            'method' => 'mercadopago',
            'transaction_id' => $preference->init_point,
        ]);

        return $preference->init_point;
    }
}
