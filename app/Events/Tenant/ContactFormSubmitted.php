<?php

namespace App\Events\Tenant;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $message,
        public ?string $source = 'web'
    ) {
    }
}
