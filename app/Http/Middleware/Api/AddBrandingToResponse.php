<?php

namespace App\Http\Middleware\Api;

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
        $response = $next($request);

        // Solo procesar respuestas JSON
        if (!$this->shouldAddBranding($response)) {
            return $response;
        }

        $content = json_decode($response->getContent(), true);

        if (!is_array($content)) {
            return $response;
        }

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
}
