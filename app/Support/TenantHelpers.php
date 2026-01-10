<?php

use App\Models\Configuration;

if (!function_exists('tenant_config')) {
    function tenant_config(string $key, mixed $default = null): mixed
    {
        $value = Configuration::where('key', $key)->value('value');

        if ($value === '') {
            return null;
        }

        return $value ?? $default;
    }
}

if (!function_exists('accepted_payment_methods')) {
    /**
     * Obtiene los métodos de pago aceptados por el tenant.
     * @return array ['transfer', 'mercadopago', 'cash']
     */
    function accepted_payment_methods(): array
    {
        $methods = [];

        if (tenant_config('payment_accepts_transfer', false)) {
            $methods[] = 'transfer';
        }

        if (tenant_config('payment_accepts_mercadopago', false)) {
            $methods[] = 'mercadopago';
        }

        if (tenant_config('payment_accepts_cash', false)) {
            $methods[] = 'cash';
        }

        return $methods;
    }
}

if (!function_exists('payment_method_config')) {
    /**
     * Obtiene la configuración completa de un método de pago.
     * @param string $method 'transfer', 'mercadopago' o 'cash'
     * @return array
     */
    function payment_method_config(string $method): array
    {
        return match($method) {
            'transfer' => [
                'enabled' => (bool) tenant_config('payment_accepts_transfer', false),
                'bank_name' => tenant_config('payment_bank_name', ''),
                'account_holder' => tenant_config('payment_bank_account_holder', ''),
                'cuit_cuil' => tenant_config('payment_bank_cuit_cuil', ''),
                'cbu' => tenant_config('payment_bank_cbu', ''),
                'alias' => tenant_config('payment_bank_alias', ''),
                'instructions' => tenant_config('payment_transfer_instructions', ''),
            ],
            'mercadopago' => [
                'enabled' => (bool) tenant_config('payment_accepts_mercadopago', false),
                'access_token' => tenant_config('payment_mp_access_token', ''),
                'public_key' => tenant_config('payment_mp_public_key', ''),
                'instructions' => tenant_config('payment_mp_instructions', ''),
            ],
            'cash' => [
                'enabled' => (bool) tenant_config('payment_accepts_cash', false),
                'instructions' => tenant_config('payment_cash_instructions', ''),
            ],
            default => ['enabled' => false],
        };
    }
}
