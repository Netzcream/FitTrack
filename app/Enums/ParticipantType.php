<?php

namespace App\Enums;

enum ParticipantType: string
{
    case CENTRAL = 'central';
    case TENANT = 'tenant';
    case STUDENT = 'student';

    public function label(): string
    {
        return match($this) {
            self::CENTRAL => 'Central',
            self::TENANT => 'Tenant',
            self::STUDENT => 'Student',
        };
    }
}
