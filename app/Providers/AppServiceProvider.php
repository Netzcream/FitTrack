<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
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
        // Register policies
        $this->registerPolicies();

        // Register event listeners
        $this->registerEventListeners();

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

    /**
     * Register the application's policies.
     */
    protected function registerPolicies(): void
    {
        // Central policies
        Gate::policy(\App\Models\Central\Conversation::class, \App\Policies\Central\ConversationPolicy::class);

        // Tenant policies (will be available in tenant context)
        if (class_exists(\App\Models\Tenant\Conversation::class)) {
            Gate::policy(\App\Models\Tenant\Conversation::class, \App\Policies\Tenant\ConversationPolicy::class);
        }
    }

    /**
     * Register event listeners for messaging.
     */
    protected function registerEventListeners(): void
    {
        // Central messaging events
        Event::listen(
            \App\Events\Central\MessageSent::class,
            \App\Listeners\Central\NotifyMessageRecipients::class
        );

        // Tenant messaging events
        Event::listen(
            \App\Events\Tenant\MessageSent::class,
            \App\Listeners\Tenant\NotifyMessageRecipients::class
        );
    }
}
