<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Throwable;

class ApiDocsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            $this->buildOpenApiSpec(),
            200,
            [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Construye un documento OpenAPI 3.0 basico a partir de las rutas reales.
     */
    private function buildOpenApiSpec(): array
    {
        $paths = [];

        /** @var RoutingRoute $route */
        foreach (Route::getRoutes() as $route) {
            if (!$this->shouldDocumentRoute($route)) {
                continue;
            }

            $path = $this->normalizePath((string) $route->uri());

            $methods = array_values(array_diff($route->methods(), ['HEAD', 'OPTIONS']));
            sort($methods);

            foreach ($methods as $method) {
                $paths[$path][strtolower($method)] = $this->buildOperation($route, $path, $method);
            }
        }

        ksort($paths);

        $baseApiUrl = rtrim(url('/api'), '/');

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => sprintf('%s API', config('app.name', 'FitTrack')),
                'version' => (string) env('APP_VERSION', '1.0.0'),
                'description' => 'Documentacion JSON autogenerada desde rutas registradas en Laravel.',
            ],
            'servers' => [
                [
                    'url' => $baseApiUrl,
                    'description' => 'Base URL principal de la API',
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Sanctum token',
                    ],
                ],
                'parameters' => [
                    'TenantHeader' => [
                        'name' => 'X-Tenant-ID',
                        'in' => 'header',
                        'required' => true,
                        'description' => 'ID del tenant donde vive el token y datos del alumno.',
                        'schema' => [
                            'type' => 'string',
                        ],
                        'example' => 'your-tenant-id',
                    ],
                ],
            ],
            'security' => [
                ['bearerAuth' => []],
            ],
            'x-fittrack' => [
                'generated_at' => now()->toIso8601String(),
                'documentation_url' => url('/api/docs'),
                'public_endpoints' => [
                    'POST /auth/login',
                    'GET /docs',
                ],
                'default_required_headers' => [
                    'Authorization' => 'Bearer {token}',
                    'X-Tenant-ID' => '{tenant-id}',
                ],
            ],
            'paths' => $paths,
        ];
    }

    private function shouldDocumentRoute(RoutingRoute $route): bool
    {
        $uri = trim((string) $route->uri(), '/');

        if ($uri === '' || !str_starts_with($uri, 'api/')) {
            return false;
        }

        // Solo rutas del archivo routes/api.php (evita ruido de otros archivos).
        return in_array('api', $route->middleware(), true);
    }

    private function normalizePath(string $uri): string
    {
        $uri = '/' . ltrim($uri, '/');

        if ($uri === '/api') {
            return '/';
        }

        if (str_starts_with($uri, '/api/')) {
            return substr($uri, 4);
        }

        return $uri;
    }

    private function buildOperation(RoutingRoute $route, string $path, string $method): array
    {
        $actionName = $route->getActionName();
        [$controllerClass, $controllerMethod] = $this->parseAction($actionName);

        $doc = $this->extractDocSummaryAndDescription($controllerClass, $controllerMethod);
        $requiresAuth = $this->requiresAuth($path, $method);
        $requiresTenantHeader = $this->requiresTenantHeader($path, $method);

        $operation = [
            'tags' => [$this->resolveTag($path)],
            'operationId' => $this->buildOperationId($method, $path),
            'summary' => $doc['summary'] ?? $this->fallbackSummary($controllerMethod, $method, $path),
            'description' => $doc['description'] ?? null,
            'parameters' => $this->buildParameters($path, $requiresTenantHeader),
            'security' => $requiresAuth ? [['bearerAuth' => []]] : [],
            'responses' => $this->buildResponses($path, $method, $requiresAuth, $requiresTenantHeader),
            'x-fittrack' => [
                'controller_action' => $actionName,
                'requires_auth' => $requiresAuth,
                'requires_tenant_header' => $requiresTenantHeader,
            ],
        ];

        if (in_array($method, ['POST', 'PUT', 'PATCH'], true) && $path !== '/auth/logout') {
            $operation['requestBody'] = [
                'required' => false,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                        ],
                    ],
                ],
            ];
        }

        return $operation;
    }

    /**
     * @return array{0:?string,1:?string}
     */
    private function parseAction(string $actionName): array
    {
        if ($actionName === 'Closure' || !str_contains($actionName, '@')) {
            return [null, null];
        }

        [$controllerClass, $controllerMethod] = explode('@', $actionName, 2);

        return [$controllerClass, $controllerMethod];
    }

    /**
     * @return array{summary:?string,description:?string}
     */
    private function extractDocSummaryAndDescription(?string $controllerClass, ?string $controllerMethod): array
    {
        if (!$controllerClass || !$controllerMethod) {
            return ['summary' => null, 'description' => null];
        }

        if (!class_exists($controllerClass) || !method_exists($controllerClass, $controllerMethod)) {
            return ['summary' => null, 'description' => null];
        }

        try {
            $reflection = new ReflectionMethod($controllerClass, $controllerMethod);
            $comment = $reflection->getDocComment();
        } catch (Throwable) {
            return ['summary' => null, 'description' => null];
        }

        if (!$comment) {
            return ['summary' => null, 'description' => null];
        }

        $lines = preg_split('/\R/', $comment) ?: [];
        $cleanLines = [];

        foreach ($lines as $line) {
            $line = trim($line);
            $line = preg_replace('/^\/\*\*?/', '', $line) ?? $line;
            $line = preg_replace('/\*\/$/', '', $line) ?? $line;
            $line = preg_replace('/^\*\s?/', '', $line) ?? $line;
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '@')) {
                continue;
            }

            if (preg_match('/^(GET|POST|PUT|PATCH|DELETE|OPTIONS|HEAD)\s+\/api\//i', $line) === 1) {
                continue;
            }

            $cleanLines[] = $line;
        }

        if ($cleanLines === []) {
            return ['summary' => null, 'description' => null];
        }

        $summary = array_shift($cleanLines);
        $description = trim(implode(' ', $cleanLines));

        return [
            'summary' => $summary ?: null,
            'description' => $description !== '' ? $description : null,
        ];
    }

    private function buildParameters(string $path, bool $requiresTenantHeader): array
    {
        $parameters = [];

        if ($requiresTenantHeader) {
            $parameters[] = [
                '$ref' => '#/components/parameters/TenantHeader',
            ];
        }

        $pathParams = [];
        preg_match_all('/\{([^}]+)\}/', $path, $pathParams);

        foreach ($pathParams[1] ?? [] as $paramName) {
            $parameters[] = [
                'name' => $paramName,
                'in' => 'path',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                ],
            ];
        }

        return $parameters;
    }

    private function buildResponses(string $path, string $method, bool $requiresAuth, bool $requiresTenantHeader): array
    {
        $responses = [
            '200' => ['description' => 'OK'],
        ];

        if ($path === '/weight' && $method === 'POST') {
            $responses['201'] = ['description' => 'Created'];
        }

        if ($path === '/messages/send' && $method === 'POST') {
            $responses['201'] = ['description' => 'Created'];
        }

        if ($requiresTenantHeader) {
            $responses['400'] = ['description' => 'Missing X-Tenant-ID header'];
        }

        if ($requiresAuth) {
            $responses['401'] = ['description' => 'Unauthorized'];
        }

        $responses['404'] = ['description' => 'Not Found'];

        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $responses['422'] = ['description' => 'Validation Error'];
        }

        return $responses;
    }

    private function resolveTag(string $path): string
    {
        $firstSegment = explode('/', trim($path, '/'))[0] ?? 'system';

        return match ($firstSegment) {
            'auth' => 'Auth',
            'profile' => 'Profile',
            'plans' => 'Plans',
            'workouts' => 'Workouts',
            'weight' => 'Weight',
            'progress', 'home', 'payments' => 'Progress',
            'messages' => 'Messaging',
            'docs' => 'Documentation',
            default => ucfirst($firstSegment),
        };
    }

    private function buildOperationId(string $method, string $path): string
    {
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '_', trim($path, '/')) ?? 'endpoint';
        $slug = trim($slug, '_');

        if ($slug === '') {
            $slug = 'root';
        }

        return strtolower($method) . '_' . strtolower($slug);
    }

    private function fallbackSummary(?string $controllerMethod, string $method, string $path): string
    {
        if ($controllerMethod) {
            $label = preg_replace('/(?<!^)[A-Z]/', ' $0', $controllerMethod) ?? $controllerMethod;
            $label = str_replace('_', ' ', $label);

            return ucfirst(trim($label));
        }

        return sprintf('%s %s', strtoupper($method), $path);
    }

    private function requiresAuth(string $path, string $method): bool
    {
        if ($path === '/docs' && $method === 'GET') {
            return false;
        }

        if ($path === '/auth/login' && $method === 'POST') {
            return false;
        }

        return true;
    }

    private function requiresTenantHeader(string $path, string $method): bool
    {
        if ($path === '/docs' && $method === 'GET') {
            return false;
        }

        if ($path === '/auth/login' && $method === 'POST') {
            return false;
        }

        return true;
    }
}
