<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Central Events
        \App\Events\TenantCreatedSuccessfully::class => [
            \App\Listeners\SendTenantWelcomeMail::class,
            \App\Listeners\GenerateSSLCertificateForTenant::class,
        ],
        \App\Events\TenantCustomDomainAttached::class => [
            \App\Listeners\ProvisionCustomDomainSsl::class,
        ],
        \App\Events\Central\MessageSent::class => [
            // Add central message listeners here if needed
        ],

        // Tenant Events - Students
        \App\Events\Tenant\StudentCreated::class => [
            \App\Listeners\Tenant\SendStudentWelcomeNotification::class,
        ],

        // Tenant Events - Training Plans
        \App\Events\Tenant\TrainingPlanActivated::class => [
            \App\Listeners\Tenant\NotifyTrainingPlanActivation::class,
        ],
        \App\Events\Tenant\TrainingPlanExpiredWithoutReplacement::class => [
            \App\Listeners\Tenant\NotifyPlanExpiredWithoutReplacement::class,
        ],

        // Tenant Events - Communication
        \App\Events\Tenant\MessageSent::class => [
            \App\Listeners\Tenant\NotifyMessageRecipients::class,
        ],
        \App\Events\Tenant\MessageReceivedWhileOffline::class => [
            \App\Listeners\Tenant\NotifyOfflineMessageRecipient::class,
        ],
        \App\Events\Tenant\ContactFormSubmitted::class => [
            \App\Listeners\Tenant\NotifyContactFormSubmission::class,
        ],

        // Tenant Events - Gamification
        \App\Events\Tenant\ExerciseCompleted::class => [
            \App\Listeners\Tenant\AwardExperiencePoints::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
