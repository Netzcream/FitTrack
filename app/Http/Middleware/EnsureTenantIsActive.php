<?php

namespace App\Http\Middleware;

use App\Enums\TenantStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (!$tenant) {
            abort(404, 'Tenant no encontrado.');
        }

        match ($tenant->status) {
            TenantStatus::ACTIVE => null,
            TenantStatus::SUSPENDED => abort(403, 'Sitio suspendido.'),
            TenantStatus::INACTIVE => abort(403, 'Este sitio se encuentra inactivo.'),
            TenantStatus::DELETED => abort(404, 'Este sitio ya no existe.'),
            default => abort(403, 'Acceso denegado.'),
        };

        return $next($request);
    }
}
