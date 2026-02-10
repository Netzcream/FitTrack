<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Device;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExpoPushService
{
    /**
     * @param  Collection<int, Device>  $devices
     * @param  array<string, mixed>  $payload
     * @return array{
     *     pending_receipts: array<string, int>,
     *     targeted_count: int,
     *     sent_count: int,
     *     error_count: int,
     *     disabled: bool
     * }
     */
    public function send(Collection $devices, string $title, string $body, array $payload = []): array
    {
        $filteredDevices = $devices
            ->filter(
                fn (mixed $device): bool => $device instanceof Device
                    && is_string($device->expo_push_token)
                    && $device->expo_push_token !== ''
            )
            ->unique('expo_push_token')
            ->values();

        $targetedCount = $filteredDevices->count();

        if ($targetedCount === 0) {
            return [
                'pending_receipts' => [],
                'targeted_count' => 0,
                'sent_count' => 0,
                'error_count' => 0,
                'disabled' => false,
            ];
        }

        if (! config('services.expo.enabled', false)) {
            return [
                'pending_receipts' => [],
                'targeted_count' => $targetedCount,
                'sent_count' => 0,
                'error_count' => $targetedCount,
                'disabled' => true,
            ];
        }

        $sendUrl = (string) config('services.expo.send_url', 'https://exp.host/--/api/v2/push/send');
        $pendingReceipts = [];
        $sentCount = 0;
        $errorCount = 0;

        foreach ($filteredDevices->chunk(100) as $deviceChunk) {
            $chunk = $deviceChunk->values();

            $messages = $chunk
                ->map(function (Device $device) use ($title, $body, $payload): array {
                    return [
                        'to' => $device->expo_push_token,
                        'title' => $title,
                        'body' => Str::limit($body, 120),
                        'sound' => 'default',
                        'priority' => 'high',
                        'data' => $payload,
                    ];
                })
                ->all();

            $response = $this->expoRequest()->post($sendUrl, $messages);

            if (! $response->ok()) {
                Log::warning('Expo push send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                $errorCount += $chunk->count();

                continue;
            }

            $tickets = $response->json('data');
            if (! is_array($tickets)) {
                Log::warning('Expo push send returned an unexpected payload', [
                    'body' => $response->body(),
                ]);

                $errorCount += $chunk->count();

                continue;
            }

            foreach ($tickets as $index => $ticket) {
                $device = $chunk->get($index);

                if (! $device instanceof Device || ! is_array($ticket)) {
                    $errorCount++;

                    continue;
                }

                $status = (string) data_get($ticket, 'status');

                if ($status === 'ok') {
                    $sentCount++;

                    $receiptId = data_get($ticket, 'id');
                    if (is_string($receiptId) && $receiptId !== '') {
                        $pendingReceipts[$receiptId] = (int) $device->id;
                    }

                    $device->forceFill([
                        'last_error_code' => null,
                        'last_error_at' => null,
                    ])->save();

                    continue;
                }

                $errorCount++;
                $errorCode = (string) data_get($ticket, 'details.error', data_get($ticket, 'message', 'unknown'));
                $this->markDeviceError($device, $errorCode);
            }
        }

        return [
            'pending_receipts' => $pendingReceipts,
            'targeted_count' => $targetedCount,
            'sent_count' => $sentCount,
            'error_count' => $errorCount,
            'disabled' => false,
        ];
    }

    private function markDeviceError(Device $device, string $errorCode): void
    {
        if ($errorCode === 'DeviceNotRegistered') {
            $device->forceFill([
                'is_active' => false,
                'deactivated_at' => now(),
                'deactivation_reason' => 'DeviceNotRegistered',
                'last_error_code' => $errorCode,
                'last_error_at' => now(),
            ])->save();

            return;
        }

        $device->forceFill([
            'last_error_code' => $errorCode,
            'last_error_at' => now(),
        ])->save();
    }

    private function expoRequest(): PendingRequest
    {
        $request = Http::asJson()
            ->acceptJson()
            ->timeout(10)
            ->retry(2, 250);

        $accessToken = (string) config('services.expo.access_token', '');
        if ($accessToken !== '') {
            $request = $request->withToken($accessToken);
        }

        return $request;
    }
}
