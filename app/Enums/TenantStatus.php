<?php

namespace App\Enums;

enum TenantStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case INACTIVE = 'inactive';
    case DELETED = 'deleted';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => __('tenant.status.active'),
            self::SUSPENDED => __('tenant.status.suspended'),
            self::INACTIVE => __('tenant.status.inactive'),
            self::DELETED => __('tenant.status.deleted'),
        };
    }
}
