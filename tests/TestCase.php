<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Stancl\Tenancy\Tenancy;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Current tenant instance for tests
     */
    protected ?Tenant $tenant = null;

    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {

        // Ensure the test database exists before running migrations
        $dbName = env('DB_DATABASE', 'test_fittrack');
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbUser = env('DB_USERNAME', 'test_fittrack');
        $dbPass = env('DB_PASSWORD', '');

        try {
            $pdo = new \PDO("mysql:host=$dbHost;port=$dbPort", $dbUser, $dbPass);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        } catch (\Exception $e) {
            echo "\n[ERROR] Could not create test database: ".$e->getMessage()."\n";
            exit(1);
        }

        parent::setUp();
    }

    /**
     * Create a test tenant and initialize tenancy context
     *
     * Usage:
     *   $this->actingAsTenant(Tenant::factory()->create())
     *   $this->actingAsTenant() // Creates a new tenant
     */
    public function actingAsTenant(?Tenant $tenant = null): Tenant
    {
        if ($tenant === null) {
            $tenant = Tenant::factory()->create();
        }

        $this->tenant = $tenant;

        // Initialize tenancy context for this tenant
        tenancy()->initialize($tenant);

        return $tenant;
    }

    /**
     * Run code in tenant context
     *
     * Usage:
     *   $this->inTenant($tenant, function() {
     *       $student = Student::first();
     *   });
     */
    public function inTenant(Tenant $tenant, callable $callback): mixed
    {
        $previousTenant = tenancy()->initialized ? app('tenant') : null;

        try {
            tenancy()->initialize($tenant);
            return $callback();
        } finally {
            if ($previousTenant) {
                tenancy()->initialize($previousTenant);
            } else {
                tenancy()->end();
            }
        }
    }

    /**
     * Teardown: clean tenancy context
     */
    protected function tearDown(): void
    {
        try {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        } finally {
            parent::tearDown();
        }
    }
}
