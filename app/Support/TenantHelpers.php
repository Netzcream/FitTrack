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
