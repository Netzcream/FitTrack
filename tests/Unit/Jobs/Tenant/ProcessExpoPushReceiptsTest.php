<?php

namespace Tests\Unit\Jobs\Tenant;

use App\Jobs\Tenant\ProcessExpoPushReceipts;
use App\Models\Tenant\Device;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProcessExpoPushReceiptsTest extends TestCase
{
    public function test_device_is_deactivated_when_expo_receipt_reports_device_not_registered(): void
    {
        Notification::fake();
        $tenant = $this->actingAsTenant();

        $user = User::factory()->create();

        $device = Device::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'platform' => 'android',
            'expo_push_token' => 'ExponentPushToken[receipt-invalid-device]',
            'is_active' => true,
            'last_seen_at' => now(),
        ]);

        config()->set('services.expo.enabled', true);
        config()->set('services.expo.receipts_url', 'https://exp.host/--/api/v2/push/getReceipts');

        Http::fake([
            'https://exp.host/--/api/v2/push/getReceipts' => Http::response([
                'data' => [
                    'receipt-1' => [
                        'status' => 'error',
                        'message' => 'The recipient device is not registered.',
                        'details' => [
                            'error' => 'DeviceNotRegistered',
                        ],
                    ],
                ],
            ], 200),
        ]);

        tenancy()->end();

        $job = new ProcessExpoPushReceipts($tenant->id, ['receipt-1' => $device->id]);
        $job->handle();

        tenancy()->initialize($tenant);
        $device->refresh();

        $this->assertFalse($device->is_active);
        $this->assertSame('DeviceNotRegistered', $device->deactivation_reason);
        $this->assertSame('DeviceNotRegistered', $device->last_error_code);
        $this->assertNotNull($device->deactivated_at);
        tenancy()->end();
    }
}
