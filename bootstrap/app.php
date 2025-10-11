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
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
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

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
