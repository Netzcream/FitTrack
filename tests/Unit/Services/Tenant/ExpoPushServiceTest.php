<?php

namespace Tests\Unit\Services\Tenant;

use App\Models\Tenant\Device;
use App\Models\User;
use App\Services\Tenant\ExpoPushService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ExpoPushServiceTest extends TestCase
{
    public function test_it_sends_push_and_returns_pending_receipts(): void
    {
        Notification::fake();
        $tenant = $this->actingAsTenant();

        $user = User::factory()->create();
        $device = Device::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'platform' => 'android',
            'expo_push_token' => 'ExponentPushToken[service-success-device]',
            'is_active' => true,
            'last_seen_at' => now(),
            'last_error_code' => 'OldError',
            'last_error_at' => now()->subHour(),
        ]);

        config()->set('services.expo.enabled', true);
        config()->set('services.expo.send_url', 'https://exp.host/--/api/v2/push/send');

        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response([
                'data' => [
                    [
                        'status' => 'ok',
                        'id' => 'receipt-ok-1',
                    ],
                ],
            ], 200),
        ]);

        $result = app(ExpoPushService::class)->send(
            devices: collect([$device]),
            title: 'FitTrack',
            body: 'Recordatorio de sesion',
            payload: ['type' => 'manual.push']
        );

        $this->assertSame(1, $result['targeted_count']);
        $this->assertSame(1, $result['sent_count']);
        $this->assertSame(0, $result['error_count']);
        $this->assertFalse($result['disabled']);
        $this->assertSame(['receipt-ok-1' => $device->id], $result['pending_receipts']);

        $device->refresh();
        $this->assertNull($device->last_error_code);
        $this->assertNull($device->last_error_at);
    }

    public function test_it_deactivates_device_when_expo_reports_device_not_registered(): void
    {
        Notification::fake();
        $tenant = $this->actingAsTenant();

        $user = User::factory()->create();
        $device = Device::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'platform' => 'ios',
            'expo_push_token' => 'ExponentPushToken[service-invalid-device]',
            'is_active' => true,
            'last_seen_at' => now(),
        ]);

        config()->set('services.expo.enabled', true);
        config()->set('services.expo.send_url', 'https://exp.host/--/api/v2/push/send');

        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response([
                'data' => [
                    [
                        'status' => 'error',
                        'message' => 'The recipient device is not registered.',
                        'details' => [
                            'error' => 'DeviceNotRegistered',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = app(ExpoPushService::class)->send(
            devices: collect([$device]),
            title: 'FitTrack',
            body: 'Mensaje de prueba'
        );

        $this->assertSame(1, $result['targeted_count']);
        $this->assertSame(0, $result['sent_count']);
        $this->assertSame(1, $result['error_count']);
        $this->assertSame([], $result['pending_receipts']);

        $device->refresh();
        $this->assertFalse($device->is_active);
        $this->assertSame('DeviceNotRegistered', $device->deactivation_reason);
        $this->assertSame('DeviceNotRegistered', $device->last_error_code);
        $this->assertNotNull($device->deactivated_at);
    }

    public function test_it_returns_disabled_result_when_push_is_disabled(): void
    {
        Notification::fake();
        $tenant = $this->actingAsTenant();

        $user = User::factory()->create();
        $device = Device::query()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'platform' => 'android',
            'expo_push_token' => 'ExponentPushToken[service-disabled-device]',
            'is_active' => true,
            'last_seen_at' => now(),
        ]);

        config()->set('services.expo.enabled', false);

        Http::fake();

        $result = app(ExpoPushService::class)->send(
            devices: collect([$device]),
            title: 'FitTrack',
            body: 'Mensaje de prueba'
        );

        $this->assertTrue($result['disabled']);
        $this->assertSame(1, $result['targeted_count']);
        $this->assertSame(0, $result['sent_count']);
        $this->assertSame(1, $result['error_count']);
        $this->assertSame([], $result['pending_receipts']);
        Http::assertNothingSent();
    }
}
