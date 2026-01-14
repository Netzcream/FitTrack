<?php

namespace App\Http\Middleware\Api;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTenancy
{
    /**
     * Handle an incoming request.
     *
     * Middleware para inicializar el contexto del tenant en rutas API.
     * Lee el header X-Tenant-ID y activa el tenant correspondiente.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener el tenant ID del header
        $tenantId = $request->header('X-Tenant-ID');

        if (!$tenantId) {
            return response()->json([
                'error' => 'Tenant ID requerido. Incluye el header X-Tenant-ID en tu petición.'
            ], 400);
        }

        // Buscar el tenant por ID
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json([
                'error' => 'Tenant no encontrado.'
            ], 404);
        }

        // Inicializar el contexto del tenant
        tenancy()->initialize($tenant);

        return $next($request);
    }
}
