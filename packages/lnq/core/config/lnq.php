<?php

return [
    // Dominios centrales (se usan por PreventAccessFromCentralDomains)
    'central_domains' => array_filter(array_map('trim', explode(',', env('CENTRAL_DOMAINS', 'localhost')))),

    // Opcionales/extensiones
    'features' => [
        'roles' => true,     // spatie/permission base en tenants
        'ui'    => true,     // layouts base
    ],
];
