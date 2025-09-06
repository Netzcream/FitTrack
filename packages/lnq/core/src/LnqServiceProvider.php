<?php

namespace Lnq\Core;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class LnqServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Config del paquete (merge sin sobreescribir valores del proyecto)
        $this->mergeConfigFrom(__DIR__ . '/../config/lnq.php', 'lnq');
    }

    public function boot(Router $router): void
    {
        // Vistas & layouts
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'lnq');

        // Rutas central & tenant (separadas)
        $this->loadRoutesFrom(__DIR__ . '/../routes/central.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/tenant.php');

        // Migraciones landlord/compartidas
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publicaciones (si querés copiarlas al app)
        $this->publishes([
            __DIR__ . '/../config/lnq.php' => config_path('lnq.php'),
        ], 'lnq-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/lnq'),
        ], 'lnq-views');

        // --- Tenancy: path de migraciones para tenants ---
        // Opción A (recomendada): publicar al proyecto para que corran con tus comandos habituales
        $this->publishes([
            __DIR__ . '/../database/migrations-tenant' => base_path('database/migrations/tenant/core'),
        ], 'lnq-tenant-migrations');

        // Opción B (avanzada): si usás un registrar dinámico de paths de Stancl, hacerlo aquí.
        // Queda comentado hasta que definamos tu estrategia de ejecución de tenants:migrate.
        // Tenancy::addTenantMigrationPath(base_path('vendor/lnq/core/database/migrations-tenant'));
        // (depende de cómo tengas configurado config/tenancy.php)
        // -------------------------------------------------

        // --- Middlewares tenancy para rutas tenant ---
        // Alias explícitos (Laravel 12 sin kernel.php)
        $router->aliasMiddleware('tenancy.init', InitializeTenancyByDomain::class);
        $router->aliasMiddleware('tenancy.prevent-central', PreventAccessFromCentralDomains::class);

        // Si querés un grupo propio, podés “definirlo” vía macro de routes en tenant.php (ver abajo).
    }
}
