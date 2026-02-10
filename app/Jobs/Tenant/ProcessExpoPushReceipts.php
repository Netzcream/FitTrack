<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessExpoPushReceipts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @param  array<string, int>  $receiptDeviceMap
     */
    public function __construct(
        public string $tenantId,
        public array $receiptDeviceMap,
        public int $attempt = 1
    ) {}

    public function handle(): void
    {
        if (! config('services.expo.enabled', false)) {
            return;
        }

        if ($this->receiptDeviceMap === []) {
            return;
        }

        $initialized = false;

        try {
            if ($this->tenantId !== '') {
                tenancy()->initialize($this->tenantId);
                $initialized = true;
            }

            $receiptsUrl = (string) config('services.expo.receipts_url', 'https://exp.host/--/api/v2/push/getReceipts');

            $response = $this->expoRequest()->post($receiptsUrl, [
                'ids' => array_keys($this->receiptDeviceMap),
            ]);

            if (! $response->ok()) {
                Log::warning('Expo receipts request failed', [
                    'tenant_id' => $this->tenantId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'attempt' => $this->attempt,
                ]);

                $this->redispatch($this->receiptDeviceMap);

                return;
            }

            $receipts = $response->json('data');
            if (! is_array($receipts)) {
                Log::warning('Expo receipts returned an unexpected payload', [
                    'tenant_id' => $this->tenantId,
                    'body' => $response->body(),
                    'attempt' => $this->attempt,
                ]);

                $this->redispatch($this->receiptDeviceMap);

                return;
            }

            $pending = [];

            foreach ($this->receiptDeviceMap as $receiptId => $deviceId) {
                $receipt = $receipts[$receiptId] ?? null;

                if (! is_array($receipt)) {
                    $pending[$receiptId] = $deviceId;

                    continue;
                }

                $status = (string) data_get($receipt, 'status');

                if ($status === 'ok') {
                    Device::query()
                        ->whereKey($deviceId)
                        ->update([
                            'last_error_code' => null,
                            'last_error_at' => null,
                        ]);

                    continue;
                }

                $errorCode = (string) data_get($receipt, 'details.error', data_get($receipt, 'message', 'unknown'));
                $this->markDeviceError($deviceId, $errorCode);
            }

            $this->redispatch($pending);
        } finally {
            if ($initialized) {
                tenancy()->end();
            }
        }
    }

    /**
     * @param  array<string, int>  $pending
     */
    private function redispatch(array $pending): void
    {
        if ($pending === [] || $this->attempt >= 5) {
            return;
        }

        self::dispatch($this->tenantId, $pending, $this->attempt + 1)
            ->delay(now()->addMinutes(2));
    }

    private function markDeviceError(int $deviceId, string $errorCode): void
    {
        if ($errorCode === 'DeviceNotRegistered') {
            Device::query()
                ->whereKey($deviceId)
                ->update([
                    'is_active' => false,
                    'deactivated_at' => now(),
                    'deactivation_reason' => 'DeviceNotRegistered',
                    'last_error_code' => $errorCode,
                    'last_error_at' => now(),
                ]);

            return;
        }

        Device::query()
            ->whereKey($deviceId)
            ->update([
                'last_error_code' => $errorCode,
                'last_error_at' => now(),
            ]);
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
