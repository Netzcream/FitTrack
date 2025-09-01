<?php

namespace App\Listeners;

use App\Events\TenantCreatedSuccessfully;
use App\Mail\TenantWelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendTenantWelcomeEmail;

class SendTenantWelcomeMail
{
    public function handle(TenantCreatedSuccessfully $event): void
    {
        $adminEmail = 'admin@' . $event->domain;
        if ($event->tenant->admin_email) {
            $adminEmail = $event->tenant->admin_email;
        }

        $password = 'password123';

        SendTenantWelcomeEmail::dispatch(
            domain: $event->domain,
            adminEmail: $adminEmail,
            password: $password
        );
    }
}
