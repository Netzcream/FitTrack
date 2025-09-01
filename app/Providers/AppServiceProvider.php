<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\DebugSession;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {



        /*if (app()->environment('local')) {
            config([
                'session.domain' => null,
                'session.secure' => false,
            ]);
        }

        if (tenant()) {
            Config::set('session.connection', 'central');
        }*/
    }
}
