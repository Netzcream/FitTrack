<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Laravel\Sanctum\PersonalAccessToken;

$token = '4|y4RJhbtz8jfCPmn37Pumb8aYrCz5bqpMVCtVV9EN3d7eb264';
$found = PersonalAccessToken::findToken($token);

if ($found) {
    echo "✅ Token encontrado en BD\n";
    echo "User ID: {$found->tokenable_id}\n";
} else {
    echo "❌ Token NO encontrado en BD\n";
    echo "Tokens en BD:\n";
    PersonalAccessToken::all()->each(function($t) {
        echo "  - Token: " . substr($t->token, 0, 10) . "... | User: {$t->tokenable_id}\n";
    });
}
