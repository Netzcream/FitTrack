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

        try {
            $content = json_decode($response->getContent(), true);

            // Agregar branding a la respuesta
            $content['branding'] = BrandingService::getBrandingData();

            $response->setContent(json_encode($content));
        } catch (\Exception $e) {
            // Si hay error, retornar response sin modificar
            return $response;
        }

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
        return str_contains($response->headers->get('Content-Type') ?? '', 'application/json');
    }
}
