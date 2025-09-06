<?php

namespace App\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $mobile;
    public $messageContent;

    public function __construct($name, $email, $mobile, $messageContent)
    {
        $this->name = $name;
        $this->email = $email;
        $this->mobile = $mobile;
        $this->messageContent = $messageContent;
    }

    public function build()
    {
        return $this->from(
            'services@luniqo.com',
            tenant('name') ?? config('app.name')
        )
            ->subject('Nuevo mensaje desde formulario de contacto')
            ->markdown('emails.tenant.contact-form')->with([
                'tenantName' => tenant('name') ?? config('app.name'),
                'contactEmail' => tenant('contact_email') ?? 'services@luniqo.com',
            ]);
    }
}
