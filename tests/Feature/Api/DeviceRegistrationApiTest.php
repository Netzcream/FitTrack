<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DeviceRegistrationApiTest extends TestCase
{
    public function test_device_can_be_registered_for_authenticated_user(): void
    {
        Notification::fake();
        $tenant = $this->actingAsTenant();

        $user = User::factory()->create();
        $token = $user->createToken('api-device-register-test')->plainTextToken;

        $response = $this
            ->withHeaders($this->apiHeaders($token, $tenant->id))
            ->postJson('/api/devices/register', [
                'expo_push_token' => 'ExponentPushToken[fittrack-device-token]',
                'platform' => 'android',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('user_id', $user->id);
        $response->assertJsonPath('tenant_id', $tenant->id);
        $response->assertJsonPath('platform', 'android');
        $response->assertJsonPath('is_active', true);

        tenancy()->initialize($tenant);
        $this->assertDatabaseHas('devices', [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'expo_push_token' => 'ExponentPushToken[fittrack-device-token]',
            'platform' => 'android',
            'is_active' => 1,
        ], 'tenant');
        tenancy()->end();
    }

    public function test_device_register_upserts_existing_token(): void
    {
        Notification::fake();
        $tenant = $this->actingAsTenant();

        $user = User::factory()->create();
        $token = $user->createToken('api-device-register-upsert-test')->plainTextToken;

        tenancy()->initialize($tenant);
        \App\Models\Tenant\Device::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'platform' => 'ios',
            'expo_push_token' => 'ExponentPushToken[fittrack-upsert-token]',
            'last_seen_at' => now()->subDay(),
            'is_active' => false,
            'deactivation_reason' => 'DeviceNotRegistered',
        ]);
        tenancy()->end();

        $response = $this
            ->withHeaders($this->apiHeaders($token, $tenant->id))
            ->postJson('/api/devices/register', [
                'expo_push_token' => 'ExponentPushToken[fittrack-upsert-token]',
                'platform' => 'ios',
                'last_seen_at' => now()->toIso8601String(),
            ]);

        $response->assertOk();
        $response->assertJsonPath('is_active', true);

        tenancy()->initialize($tenant);
        $this->assertDatabaseCount('devices', 1, 'tenant');
        $this->assertDatabaseHas('devices', [
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'expo_push_token' => 'ExponentPushToken[fittrack-upsert-token]',
            'is_active' => 1,
            'deactivation_reason' => null,
        ], 'tenant');
        tenancy()->end();
    }

    /**
     * @return array<string, string>
     */
    private function apiHeaders(string $token, string $tenantId): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-ID' => $tenantId,
        ];
    }
}
