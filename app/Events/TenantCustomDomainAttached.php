<?php

namespace App\Events;

use App\Models\Tenant;

class TenantCustomDomainAttached
{
    public function __construct(
        public Tenant $tenant,
        public string $domain
    ) {}
}
