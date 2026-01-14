<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

// Encontrar el tenant sabrina
$tenant = Tenant::find('sabrina');
if (!$tenant) {
    echo "❌ Tenant 'sabrina' no encontrado\n";
    exit(1);
}
echo "✅ Tenant encontrado: {$tenant->id}\n";

// Inicializar tenancy
tenancy()->initialize($tenant);
echo "✅ Tenancy inicializado\n";

// Buscar el usuario
$user = \App\Models\User::where('email', 'juan@example.com')->first();
if (!$user) {
    echo "❌ Usuario juan@example.com no encontrado\n";
    exit(1);
}
echo "✅ Usuario encontrado: {$user->name} ({$user->email})\n";

// Cambiar contraseña
$user->password = Hash::make('123456');
$user->save();
echo "✅ Contraseña actualizada a: 123456\n";

// Crear un token de prueba (los tokens se guardan en la BD actual - del tenant)
$token = $user->createToken('api-test')->plainTextToken;
echo "✅ Token creado\n";

// Mostrar datos para el login
echo "\n=== DATOS PARA LOGIN ===\n";
echo "Email: juan@example.com\n";
echo "Contraseña: 123456\n";
echo "Tenant ID: {$tenant->id}\n";
echo "Token: {$token}\n";
echo "\nPuedes usar este token directamente o hacer login para obtener uno nuevo.\n";
