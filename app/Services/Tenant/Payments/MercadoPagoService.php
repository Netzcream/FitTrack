<?php

namespace App\Services\Tenant\Payments;

use App\Models\Tenant\Payment;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

class MercadoPagoService
{
    public function __construct()
    {
        $token = tenant_config('mercadopago_access_token');
        if (!$token) {
            throw new \RuntimeException('MercadoPago Access Token no configurado para este tenant');
        }

        MercadoPagoConfig::setAccessToken($token);
    }

    public function createPaymentLink(Payment $payment): string
    {
        $client = new PreferenceClient();

        $preference = $client->create([
            'items' => [[
                'title' => 'Abono mensual',
                'quantity' => 1,
                'unit_price' => (float) $payment->amount,
                'currency_id' => 'ARS',
            ]],
            'payer' => [
                'email' => $payment->student->email ?? '',
            ],
            'back_urls' => [
                'success' => route('tenant.student.payments'),
                'failure' => route('tenant.student.payments'),
                'pending' => route('tenant.student.payments'),
            ],
            'auto_return' => 'approved',
            'external_reference' => 'PAY-' . $payment->id,
        ]);

        $payment->update([
            'method' => 'mercadopago',
            'transaction_id' => $preference->init_point,
        ]);

        return $preference->init_point;
    }
}
