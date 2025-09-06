<?php

$host = parse_url(env('APP_URL'), PHP_URL_HOST);          // ej: luniqo.com.ar
$escapedHost = $host ? preg_quote($host, '/') : null;     // escapar puntos, etc.

// https? => permite http y https (cambiá a ^https:// si querés forzar https)
// ([a-z0-9-]+\.)* => cualquier subdominio (0 o más niveles), incluye el apex
// (:\d+)? => puerto opcional
$dynamicCorsPattern = $escapedHost
    ? '/^https?:\/\/([a-z0-9-]+\.)*' . $escapedHost . '(:\d+)?$/i'
    : null;

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'login', 'build/*', 'sanctum/csrf-cookie', 'logout', 'livewire/*', '/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => array_filter([
        $dynamicCorsPattern,
    ]),

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
