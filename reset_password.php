<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

$tenant = Tenant::find('sabrina');
tenancy()->initialize($tenant);

$user = \App\Models\User::where('email', 'juan@example.com')->first();

if ($user) {
    $user->password = Hash::make('test123');
    $user->save();
    echo "✅ Contraseña actualizada a: test123\n";
} else {
    echo "❌ Usuario no encontrado\n";
}
