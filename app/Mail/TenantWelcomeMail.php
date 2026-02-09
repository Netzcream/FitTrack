<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenantWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $domain;
    public string $adminEmail;
    public ?string $password;

    public function __construct(string $domain, string $adminEmail, ?string $password)
    {
        $this->domain = $domain;
        $this->adminEmail = $adminEmail;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Tu acceso a Fittrack está listo')
            ->markdown('emails.tenant.welcome');
    }
}
