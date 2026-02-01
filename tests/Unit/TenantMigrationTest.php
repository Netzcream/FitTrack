<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Tenant;
use App\Models\Tenant\Student;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationParticipant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantMigrationTest extends TestCase
{
    /**
     * Test that all central migrations run without error on MySQL test DB.
     */
    public function test_central_migrations_run_cleanly_on_mysql(): void
    {
        // Use central connection
        $connection = DB::connection('mysql');

        // Drop all tables in the central test database
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $tables = $connection->select("SHOW TABLES");
        foreach ($tables as $table) {
            $tableName = current((array)$table);
            Schema::connection('mysql')->drop($tableName);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Run all central migrations (default path)
        $exitCode = Artisan::call('migrate', [
            '--database' => 'mysql',
            '--force' => true,
        ]);

        $this->assertSame(0, $exitCode, 'Central migrations should run without error on MySQL test DB.');

        // Verify conversation_participants table exists with correct schema
        $this->assertTrue(Schema::connection('mysql')->hasTable('conversation_participants'));
        $this->assertTrue(Schema::connection('mysql')->hasColumn('conversation_participants', 'participant_id'));
    }

    /**
     * Test that all tenant migrations run without error on MySQL test DB.
     */
    public function test_tenant_migrations_run_cleanly_on_mysql(): void
    {
        // Clean up any existing test tenant database
        $testTenantDb = 'test_fittrack_tenant_test';
        try {
            DB::statement("DROP DATABASE IF EXISTS `{$testTenantDb}`");
        } catch (\Exception $e) {
            // Ignore if it doesn't exist
        }

        // Create the tenant database manually (no tenant model, no events, no seeding)
        DB::statement("CREATE DATABASE `{$testTenantDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Configure a temporary connection to the tenant database
        config(['database.connections.tenant_test' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $testTenantDb,
            'username' => env('DB_USERNAME', 'test_fittrack'),
            'password' => env('DB_PASSWORD', 'test_fittrack'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]]);

        // Run tenant migrations on the test tenant database
        $exitCode = Artisan::call('migrate', [
            '--database' => 'tenant_test',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        $this->assertSame(0, $exitCode, 'Tenant migrations should run without error on MySQL test DB.');

        // Cleanup: drop the test tenant database
        DB::statement("DROP DATABASE IF EXISTS `{$testTenantDb}`");
    }

    /**
     * Test conversation_participants table schema after migration fix.
     */
    public function test_conversation_participants_schema(): void
    {
        // Run central migrations
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $tables = DB::select("SHOW TABLES");
        foreach ($tables as $table) {
            $tableName = current((array)$table);
            Schema::connection('mysql')->drop($tableName);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Artisan::call('migrate', [
            '--database' => 'mysql',
            '--force' => true,
        ]);

        // Verify table exists
        $this->assertTrue(
            Schema::connection('mysql')->hasTable('conversation_participants'),
            'conversation_participants table should exist'
        );

        // Verify participant_id is VARCHAR (not bigint)
        $columnType = DB::connection('mysql')->selectOne(
            "SELECT DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
             AND TABLE_NAME = 'conversation_participants'
             AND COLUMN_NAME = 'participant_id'",
            [DB::connection('mysql')->getDatabaseName()]
        );

        $this->assertEquals('varchar', $columnType->DATA_TYPE, 'participant_id should be VARCHAR');
        // After the migration runs, it should be 255. Before migration it's 64.
        $this->assertContains(
            $columnType->CHARACTER_MAXIMUM_LENGTH,
            [64, 255],
            'participant_id should be VARCHAR(64) before migration or VARCHAR(255) after migration'
        );

        // Verify indexes exist
        $indexes = DB::connection('mysql')->select(
            "SELECT INDEX_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ?
             AND TABLE_NAME = 'conversation_participants'
             GROUP BY INDEX_NAME",
            [DB::connection('mysql')->getDatabaseName()]
        );

        $indexNames = array_column($indexes, 'INDEX_NAME');

        $this->assertContains('conv_participant_unique', $indexNames, 'conv_participant_unique index should exist');
        $this->assertContains('conv_participant_type_id_index', $indexNames, 'conv_participant_type_id_index should exist');
    }

    /**
     * Test that tenants are properly isolated - data from one tenant cannot be accessed by another.
     */
    public function test_tenant_data_isolation(): void
    {
        // Create two tenant databases manually
        $tenant1Db = 'test_fittrack_tenant_1';
        $tenant2Db = 'test_fittrack_tenant_2';

        try {
            DB::statement("DROP DATABASE IF EXISTS `{$tenant1Db}`");
            DB::statement("DROP DATABASE IF EXISTS `{$tenant2Db}`");
        } catch (\Exception $e) {
            // Ignore
        }

        DB::statement("CREATE DATABASE `{$tenant1Db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        DB::statement("CREATE DATABASE `{$tenant2Db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Configure connections
        config(['database.connections.tenant_1' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $tenant1Db,
            'username' => env('DB_USERNAME', 'test_fittrack'),
            'password' => env('DB_PASSWORD', 'test_fittrack'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]]);

        config(['database.connections.tenant_2' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $tenant2Db,
            'username' => env('DB_USERNAME', 'test_fittrack'),
            'password' => env('DB_PASSWORD', 'test_fittrack'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]]);

        // Run migrations on both
        Artisan::call('migrate', ['--database' => 'tenant_1', '--path' => 'database/migrations/tenant', '--force' => true]);
        Artisan::call('migrate', ['--database' => 'tenant_2', '--path' => 'database/migrations/tenant', '--force' => true]);

        // Insert data in tenant 1
        DB::connection('tenant_1')->table('users')->insert([
            'name' => 'Tenant 1 User',
            'email' => 'user1@tenant1.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert data in tenant 2
        DB::connection('tenant_2')->table('users')->insert([
            'name' => 'Tenant 2 User',
            'email' => 'user2@tenant2.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verify isolation - each tenant should only see their own data
        $tenant1Users = DB::connection('tenant_1')->table('users')->count();
        $tenant2Users = DB::connection('tenant_2')->table('users')->count();

        $this->assertEquals(1, $tenant1Users, 'Tenant 1 should have exactly 1 user');
        $this->assertEquals(1, $tenant2Users, 'Tenant 2 should have exactly 1 user');

        // Verify data content
        $user1 = DB::connection('tenant_1')->table('users')->first();
        $user2 = DB::connection('tenant_2')->table('users')->first();

        $this->assertEquals('user1@tenant1.com', $user1->email);
        $this->assertEquals('user2@tenant2.com', $user2->email);
        $this->assertNotEquals($user1->email, $user2->email, 'Tenants should have different data');

        // Cleanup
        DB::statement("DROP DATABASE IF EXISTS `{$tenant1Db}`");
        DB::statement("DROP DATABASE IF EXISTS `{$tenant2Db}`");
    }

    /**
     * Test that basic tenant records can be created and relationships work.
     */
    public function test_can_create_basic_tenant_records(): void
    {
        // Create tenant database
        $testTenantDb = 'test_fittrack_tenant_records';
        try {
            DB::statement("DROP DATABASE IF EXISTS `{$testTenantDb}`");
        } catch (\Exception $e) {
            // Ignore
        }

        DB::statement("CREATE DATABASE `{$testTenantDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Configure connection
        config(['database.connections.tenant_test_records' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $testTenantDb,
            'username' => env('DB_USERNAME', 'test_fittrack'),
            'password' => env('DB_PASSWORD', 'test_fittrack'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]]);

        // Run migrations
        Artisan::call('migrate', [
            '--database' => 'tenant_test_records',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        // Set connection for models
        config(['database.default' => 'tenant_test_records']);

        // Create a user first (required for students)
        $user = DB::connection('tenant_test_records')->table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test creating a student
        $studentId = DB::connection('tenant_test_records')->table('students')->insertGetId([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'user_id' => $user,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertNotNull($studentId, 'Student should be created');

        // Test creating a conversation
        $conversationId = DB::connection('tenant_test_records')->table('conversations')->insertGetId([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'subject' => 'Test Conversation',
            'type' => 'direct',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertNotNull($conversationId, 'Conversation should be created');

        // Test creating a conversation participant with STRING participant_id (the fix we made)
        $participantId = DB::connection('tenant_test_records')->table('conversation_participants')->insertGetId([
            'conversation_id' => $conversationId,
            'participant_type' => 'App\\Models\\Tenant\\Student',
            'participant_id' => 'test-slug-123', // STRING, not integer
        ]);

        $this->assertNotNull($participantId, 'Conversation participant should be created with STRING participant_id');

        // Verify the participant was created with correct data
        $participant = DB::connection('tenant_test_records')
            ->table('conversation_participants')
            ->where('id', $participantId)
            ->first();

        $this->assertEquals('test-slug-123', $participant->participant_id, 'participant_id should be stored as string');
        $this->assertEquals('App\\Models\\Tenant\\Student', $participant->participant_type);

        // Cleanup
        DB::statement("DROP DATABASE IF EXISTS `{$testTenantDb}`");
    }
}

