<?php

namespace App\Events\Tenant;

use App\Models\Tenant\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReceivedWhileOffline
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
        public string $recipientType, // 'student' o 'tenant'
        public int $recipientId
    ) {
    }
}
