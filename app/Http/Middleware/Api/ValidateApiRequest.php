<?php

namespace App\Http\Middleware\Api;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiRequest
{
    /**
     * Middleware para autenticación en rutas API usando Sanctum
     * Valida el token del header Authorization
     *
     * IMPORTANTE: Los tokens están en la BD del tenant especificado en X-Tenant-ID header
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skipear autenticación para rutas públicas
        if ($request->is(
            'api/auth/login',
            'api/auth/register',
            'auth/login',
            'auth/register',
            'api/docs',
            'docs'
        )) {
            return $next($request);
        }

        // Obtener el token del header Authorization
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Se requiere autenticación. Incluye header: Authorization: Bearer {token}'
            ], 401);
        }

        // Obtener el tenant ID del header
        $tenantId = $request->header('X-Tenant-ID');

        if (!$tenantId) {
            return response()->json([
                'error' => 'Tenant ID requerido',
                'message' => 'Incluye el header X-Tenant-ID en tu petición'
            ], 400);
        }

        // Buscar el tenant
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json([
                'error' => 'Tenant no encontrado'
            ], 404);
        }

        // Inicializar contexto del tenant para buscar el token en su BD
        tenancy()->initialize($tenant);

        // Buscar el token en la BD del tenant
        $personalAccessToken = PersonalAccessToken::findToken($token);

        if (!$personalAccessToken) {
            tenancy()->end();
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Token inválido o expirado'
            ], 401);
        }

        // Autenticar al usuario usando el token
        auth('sanctum')->setUser($personalAccessToken->tokenable);

        // También guardar en la request para que los controllers puedan acceder
        $request->setUserResolver(function () use ($personalAccessToken) {
            return $personalAccessToken->tokenable;
        });

        return $next($request);
    }
}

