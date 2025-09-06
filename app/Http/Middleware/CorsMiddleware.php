<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsForApexAndSubdomains
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Origin enviado por el navegador
        $origin = $request->headers->get('Origin');

        // Leemos el dominio base desde APP_URL (recomendado: config('app.url'))
        $appUrl = config('app.url'); // usa env('APP_URL') si preferís
        $allowedHost = $appUrl ? parse_url($appUrl, PHP_URL_HOST) : null; // ej: luniqo.com.ar

        if ($origin && $allowedHost) {
            // Acepta http/https, cualquier subdominio (o ninguno) y puerto opcional
            // ^https?://([subdominios].)?<host>(:puerto)?$
            $pattern = '#^https?://([a-z0-9-]+\.)*' . preg_quote($allowedHost, '#') . '(:\d+)?$#i';

            if (preg_match($pattern, $origin)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-Livewire-Navigate');
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
            }
        }

        return $response;
    }
}
