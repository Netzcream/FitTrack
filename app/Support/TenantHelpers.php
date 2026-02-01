<?php

use App\Models\Configuration;
use Illuminate\Support\Facades\Auth;

if (!function_exists('tenant_config')) {
    function tenant_config(string $key, mixed $default = null): mixed
    {
        return Configuration::conf($key, $default);
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

if (!function_exists('gamification_stats')) {
    /**
     * Helper para obtener estadísticas de gamificación de un alumno.
     *
     * @param \App\Models\Tenant\Student|int|null $student Student model, ID, o null para usuario autenticado
     * @return array
     */
    function gamification_stats($student = null): array
    {
        if ($student === null) {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            $student = $user?->student;
        }

        if (is_int($student)) {
            $student = \App\Models\Tenant\Student::find($student);
        }

        if (!$student) {
            return [
                'has_profile' => false,
                'total_xp' => 0,
                'current_level' => 0,
                'current_tier' => 0,
                'tier_name' => 'Not Rated',
                'active_badge' => 'not_rated',
                'total_exercises' => 0,
                'level_progress' => 0,
                'xp_for_next_level' => 100,
            ];
        }

        $service = new \App\Services\Tenant\GamificationService();
        return $service->getStudentStats($student);
    }
}

if (!function_exists('gamification_badge_class')) {
    /**
     * Retorna las clases CSS de Tailwind para un badge según el tier.
     *
     * @param int $tier (0-5)
     * @return string
     */
    function gamification_badge_class(int $tier): string
    {
        return match ($tier) {
            0 => 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            1 => 'bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-300',
            2 => 'bg-blue-200 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            3 => 'bg-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            4 => 'bg-purple-200 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            5 => 'bg-red-200 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-200 text-gray-800',
        };
    }
}

if (!function_exists('gamification_tier_icon')) {
    /**
     * Retorna el icono/emoji conceptual para un tier.
     *
     * @param int $tier (0-5)
     * @return string
     */
    function gamification_tier_icon(int $tier): string
    {
        return match ($tier) {
            0 => '🥚',
            1 => '🐢',
            2 => '🐕',
            3 => '🐅',
            4 => '🐺',
            5 => '🦅',
            default => '🥚',
        };
    }
}

if (!function_exists('ai_usage')) {
    /**
     * Obtiene información sobre el uso de generación con IA del tenant actual.
     *
     * @return array ['used' => int, 'limit' => int, 'available' => int, 'percentage' => float, 'has_limit' => bool, 'is_exceeded' => bool]
     */
    function ai_usage(): array
    {
        $tenant = tenant();

        if (!$tenant) {
            return [
                'used' => 0,
                'limit' => 0,
                'available' => 0,
                'percentage' => 0,
                'has_limit' => false,
                'is_exceeded' => false,
            ];
        }

        return $tenant->getAiGenerationUsage();
    }
}

if (!function_exists('ai_usage_history')) {
    /**
     * Obtiene el historial de uso de IA del tenant actual.
     *
     * @param int $months Número de meses a consultar (por defecto 12)
     * @return \Illuminate\Support\Collection
     */
    function ai_usage_history(int $months = 12): \Illuminate\Support\Collection
    {
        $tenant = tenant();

        if (!$tenant) {
            return collect();
        }

        return $tenant->getAiUsageHistory($months);
    }
}

if (!function_exists('ai_usage_stats')) {
    /**
     * Obtiene estadísticas agregadas de uso de IA del tenant actual.
     *
     * @return array ['total_usage' => int, 'avg_usage' => float, 'max_usage' => int, 'months_tracked' => int, 'total_available' => int, 'usage_percentage' => float]
     */
    function ai_usage_stats(): array
    {
        $tenant = tenant();

        if (!$tenant) {
            return [
                'total_usage' => 0,
                'avg_usage' => 0,
                'max_usage' => 0,
                'months_tracked' => 0,
                'total_available' => 0,
                'usage_percentage' => 0,
            ];
        }

        return $tenant->getAiUsageStats();
    }
}
