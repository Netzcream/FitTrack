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
        return $this->from(
            env('MAIL_FROM_ADDRESS', 'notifications@fittrack.com.ar'),
            tenant('name') ?? config('app.name')
        )
            ->subject('Nuevo mensaje desde formulario de contacto')
            ->markdown('emails.central.contact-form');
    }
}
