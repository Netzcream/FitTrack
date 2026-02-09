<?php

namespace App\Listeners;

use App\Events\TenantCreatedSuccessfully;
use App\Jobs\SendTenantWelcomeEmail;

class SendTenantWelcomeMail
{
    public function handle(TenantCreatedSuccessfully $event): void
    {
        $adminEmail = 'admin@' . $event->domain;
        if ($event->tenant->admin_email) {
            $adminEmail = $event->tenant->admin_email;
        }

        SendTenantWelcomeEmail::dispatch(
            domain: $event->domain,
            adminEmail: $adminEmail,
            password: $event->adminPassword
        );
    }
}
