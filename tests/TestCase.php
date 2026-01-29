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
            // Create testing SQLite database file with absolute path BEFORE calling parent::setUp()
            // Use direct path construction since storage_path() helper isn't available yet
            $dbPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'testing.sqlite';
        if (!file_exists($dbPath)) {
            @mkdir(dirname($dbPath), 0777, true);
            touch($dbPath);
        }

        // Force the DB_DATABASE env variable to absolute path
        // This must be done before parent::setUp() which initializes the database connection
        $_ENV['DB_DATABASE'] = $dbPath;
        putenv('DB_DATABASE=' . $dbPath);

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
        $previousTenant = tenancy()->tenant();

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
            if (tenancy()->tenant()) {
                tenancy()->end();
            }
        } finally {
            parent::tearDown();
        }
    }
}
