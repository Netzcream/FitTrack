<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    /**
     * POST /api/devices/register
     * Register or refresh a device expo push token for the authenticated user.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'expo_push_token' => ['required', 'string', 'max:255', 'regex:/^(Exponent|Expo)PushToken\[[A-Za-z0-9\-_]+\]$/'],
            'platform' => ['required', 'string', Rule::in(['ios', 'android', 'web'])],
            'last_seen_at' => ['nullable', 'date'],
        ]);

        $tenantId = tenancy()->initialized ? (string) tenancy()->tenant?->id : '';
        if ($tenantId === '') {
            return response()->json([
                'error' => 'Tenant context is not initialized.',
            ], 400);
        }
        $lastSeenAt = isset($validated['last_seen_at'])
            ? Carbon::parse((string) $validated['last_seen_at'])
            : now();

        $device = Device::query()->updateOrCreate(
            ['expo_push_token' => $validated['expo_push_token']],
            [
                'user_id' => (int) $request->user()->id,
                'tenant_id' => $tenantId,
                'platform' => $validated['platform'],
                'last_seen_at' => $lastSeenAt,
                'is_active' => true,
                'deactivated_at' => null,
                'deactivation_reason' => null,
                'last_error_code' => null,
                'last_error_at' => null,
            ]
        );

        return response()->json([
            'id' => $device->id,
            'user_id' => $device->user_id,
            'tenant_id' => $device->tenant_id,
            'platform' => $device->platform,
            'expo_push_token' => $device->expo_push_token,
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
            'is_active' => $device->is_active,
        ], $device->wasRecentlyCreated ? 201 : 200);
    }
}
