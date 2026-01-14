<?php

use App\Http\Middleware\DebugSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\ErrorHandler\Debug;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // También servir API desde api.<dominio> sin prefijo /api
            \Illuminate\Support\Facades\Route::middleware('api')
                ->domain('api.' . env('APP_DOMAIN'))
                ->group(base_path('routes/api.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('universal', []);
        $middleware->alias([
            'permission'         => PermissionMiddleware::class,
            'role'               => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'tenant.auth'      => \App\Http\Middleware\Tenant\TenantAuthenticate::class,
            'tenant.active'    => \App\Http\Middleware\EnsureTenantIsActive::class,
            'tenant.student.access' => \App\Http\Middleware\Tenant\EnsureStudentAccessEnabled::class,
            'validate.api'     => \App\Http\Middleware\Api\ValidateApiRequest::class,
        ]);

        // Aplicar middleware global para API
        $middleware->api(\App\Http\Middleware\Api\ValidateApiRequest::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
