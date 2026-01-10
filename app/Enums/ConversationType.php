<?php

namespace App\Enums;

enum ConversationType: string
{
    case CENTRAL_TENANT = 'central_tenant';
    case TENANT_STUDENT = 'tenant_student';

    public function label(): string
    {
        return match($this) {
            self::CENTRAL_TENANT => 'Central ↔ Tenant',
            self::TENANT_STUDENT => 'Tenant ↔ Student',
        };
    }
}
