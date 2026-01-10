<?php

namespace App\Enums;

enum MessageStatus: string
{
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case READ = 'read';

    public function label(): string
    {
        return match($this) {
            self::SENT => 'Sent',
            self::DELIVERED => 'Delivered',
            self::READ => 'Read',
        };
    }
}
