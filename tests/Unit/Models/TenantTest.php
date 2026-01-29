<?php

namespace Tests\Unit\Models;

use App\Models\Tenant;
use Tests\TestCase;

class TenantTest extends TestCase
{
    /**
     * Test tenant creation with UUID
     */
    public function test_tenant_has_uuid(): void
    {
        $tenant = Tenant::factory()->create();

        $this->assertNotNull($tenant->id);
        $this->assertIsString($tenant->id);
        $this->assertTrue(strlen($tenant->id) === 36); // UUID format
    }

    /**
     * Test tenant has associated domain
     */
    public function test_tenant_can_have_domains(): void
    {
        $tenant = Tenant::factory()->create();

        $tenant->domains()->create([
            'domain' => 'trainer.fittrack.test',
        ]);

        $this->assertTrue($tenant->domains()->exists());
        $this->assertEquals('trainer.fittrack.test', $tenant->domains()->first()->domain);
    }

    /**
     * Test multiple tenants are isolated
     */
    public function test_multiple_tenants_are_isolated(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $this->assertNotEquals($tenant1->id, $tenant2->id);
        $this->assertTrue($tenant1->exists());
        $this->assertTrue($tenant2->exists());

        // Both should be in central DB (not in tenant DB yet)
        $this->assertEquals(2, Tenant::count());
    }
}
