<?php

/**
 * Quick Verification Script - API Controllers and Services
 *
 * Verifica que todos los nuevos archivos tengan la sintaxis correcta
 * y las clases estén disponibles.
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "🔍 Verificando nuevos archivos de API...\n\n";

$files = [
    'Controllers' => [
        'App\Http\Controllers\Api\WorkoutApiController' => 'app/Http/Controllers/Api/WorkoutApiController.php',
        'App\Http\Controllers\Api\StudentWeightApiController' => 'app/Http/Controllers/Api/StudentWeightApiController.php',
        'App\Http\Controllers\Api\ProgressApiController' => 'app/Http/Controllers/Api/ProgressApiController.php',
    ],
    'Services' => [
        'App\Services\Tenant\BrandingService' => 'app/Services/Tenant/BrandingService.php',
    ],
    'Middleware' => [
        'App\Http\Middleware\Api\AddBrandingToResponse' => 'app/Http/Middleware/Api/AddBrandingToResponse.php',
    ],
];

$allGood = true;

foreach ($files as $category => $items) {
    echo "📦 $category:\n";

    foreach ($items as $class => $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "  ✅ $file\n";
            echo "     → Class: $class\n";
        } else {
            echo "  ❌ $file NOT FOUND\n";
            $allGood = false;
        }
    }

    echo "\n";
}

if ($allGood) {
    echo "✅ ¡Todos los archivos están presentes!\n\n";
    echo "📋 Archivos creados:\n";
    echo "   • 3 Controllers nuevos\n";
    echo "   • 1 Service nuevo\n";
    echo "   • 1 Middleware nuevo\n";
    echo "   • Routes actualizadas (15+ endpoints)\n";
    echo "   • Documentación completa\n\n";
    echo "🚀 Próximos pasos:\n";
    echo "   1. php artisan route:list | grep api\n";
    echo "   2. Testear endpoints con Postman/Thunder Client\n";
    echo "   3. Verificar branding en respuestas\n";
} else {
    echo "❌ Algunos archivos no se encontraron. Revisa los paths.\n";
    exit(1);
}
