<?php

namespace App\Http\Middleware\Api;

use App\Models\Tenant;
use App\Services\Tenant\BrandingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddBrandingToResponse
{
    /**
     * Middleware que añade información de branding a todas las respuestas JSON de API.
     *
     * Transforma:
     * {
     *   "data": {...},
     *   "message": "..."
     * }
     *
     * A:
     * {
     *   "data": {...},
     *   "message": "...",
     *   "branding": {
     *     "brand_name": "...",
     *     "logo_url": "...",
     *     "primary_color": "...",
     *     ...
     *   }
     * }
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Mantener la spec OpenAPI limpia en /api/docs.
        if ($request->is('api/docs', 'docs')) {
            return $next($request);
        }

        $response = $next($request);

        // Solo procesar respuestas JSON
        if (!$this->shouldAddBranding($response)) {
            return $response;
        }

        $content = json_decode($response->getContent(), true);

        if (!is_array($content)) {
            return $response;
        }

        [$initializedHere, $previousTenantId] = $this->initializeTenantContext($request, $content);

        try {
            // Agregar branding/trainer en un bloque separado del payload principal
            $branding = BrandingService::getSafeBrandingData();
            $trainer = BrandingService::getSafeTrainerData();

            $content['branding'] = $branding;
            $content['trainer'] = $trainer;

            // Compatibilidad mobile: si el cliente usa solo response.data.data
            if (isset($content['data']) && $this->isAssociativeArray($content['data'])) {
                $content['data']['_branding'] = $branding;
                $content['data']['_trainer'] = $trainer;
            }
        } finally {
            $this->restoreTenantContext($initializedHere, $previousTenantId);
        }

        $encoded = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            return $response;
        }

        $response->setContent($encoded);

        return $response;
    }

    /**
     * Determinar si debe agregar branding a la respuesta
     */
    private function shouldAddBranding(Response $response): bool
    {
        // Solo para respuestas JSON exitosas (200-299) y errores (4xx, 5xx)
        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode > 599) {
            return false;
        }

        // Verificar si es JSON
        $contentType = $response->headers->get('Content-Type') ?? '';
        return str_contains($contentType, 'application/json') || str_contains($contentType, '+json');
    }

    /**
     * Determina si un array es asociativo (objeto JSON), no lista numerica.
     */
    private function isAssociativeArray(mixed $value): bool
    {
        if (!is_array($value) || $value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    /**
     * Garantiza contexto tenant para construir branding en cualquier endpoint API.
     * Retorna [si_inicializo_contexto_aqui, tenant_id_previo]
     */
    private function initializeTenantContext(Request $request, array $content): array
    {
        $previousTenantId = tenant()?->id;
        if ($previousTenantId !== null) {
            return [false, (string) $previousTenantId];
        }

        $tenantId = $this->resolveTenantId($request, $content);
        if ($tenantId === null) {
            return [false, null];
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return [false, null];
        }

        tenancy()->initialize($tenant);
        return [true, null];
    }

    /**
     * Restaura el contexto tenant si se inicializó dentro de este middleware.
     */
    private function restoreTenantContext(bool $initializedHere, ?string $previousTenantId): void
    {
        if (!$initializedHere) {
            return;
        }

        tenancy()->end();

        if ($previousTenantId === null) {
            return;
        }

        $previousTenant = Tenant::find($previousTenantId);
        if ($previousTenant) {
            tenancy()->initialize($previousTenant);
        }
    }

    /**
     * Resolver tenant id desde header o desde payload de login.
     */
    private function resolveTenantId(Request $request, array $content): ?string
    {
        $fromHeader = trim((string) $request->header('X-Tenant-ID', ''));
        if ($fromHeader !== '') {
            return $fromHeader;
        }

        $fromPayload = data_get($content, 'tenant.id');
        if (is_string($fromPayload) && trim($fromPayload) !== '') {
            return trim($fromPayload);
        }

        return null;
    }
}
