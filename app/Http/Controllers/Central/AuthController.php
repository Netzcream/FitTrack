<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validación básica
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        /*
        |--------------------------------------------------------------------------
        | 1) Detectar automáticamente a qué tenant pertenece el usuario
        |--------------------------------------------------------------------------
        |
        | NO usamos data->users.
        | Recorremos todos los tenants y buscamos el usuario dentro de cada uno.
        | Cuando lo encontramos, listo: ya sabemos el tenant real.
        |
        */

        $tenant = null;
        $user   = null;

        foreach (Tenant::all() as $t) {

            // Cambiar contexto al tenant
            tenancy()->initialize($t);

            // Buscar el usuario en la DB del tenant actual
            $u = \App\Models\User::where('email', $request->email)->first();

            if ($u) {
                $tenant = $t;
                $user   = $u;
                break;
            }
        }

        if (!$tenant || !$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | 2) Validar contraseña dentro del tenant correcto
        |--------------------------------------------------------------------------
        */

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 3) Crear token Sanctum dentro del tenant
        |--------------------------------------------------------------------------
        */

        $token = $user->createToken('pwa')->plainTextToken;

        /*
        |--------------------------------------------------------------------------
        | 4) Responder a la PWA con el tenant real + token
        |--------------------------------------------------------------------------
        */

        $scheme = app()->environment('local') ? 'http' : 'https';

        return response()->json([
            'tenant'        => $tenant->id,
            'tenant_domain' => "{$scheme}://{$tenant->id}.fittrack.test",
            'token'         => $token
        ]);
    }
}
