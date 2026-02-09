<?php

namespace App\Mail\Central;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactForm extends Mailable
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
        $brandUrl = rtrim((string) config('app.url', 'https://fittrack.com.ar'), '/');
        $appName = (string) config('app.name', 'FitTrack');
        $fromAddress = (string) config('mail.from.address', env('MAIL_FROM_ADDRESS', 'notifications@fittrack.com.ar'));

        return $this->from(
            $fromAddress,
            $appName
        )
            ->subject('Nuevo mensaje desde formulario de contacto')
            ->markdown('emails.central.contact-form')
            ->with([
                'tenantName' => $appName,
                'contactEmail' => $fromAddress,
                'brandUrl' => $brandUrl,
                'logoUrl' => $brandUrl . '/images/fittrack-icon-only.png',
            ]);
    }
}
