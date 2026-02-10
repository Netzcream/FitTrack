<?php

namespace App\Listeners\Tenant;

use App\Enums\ParticipantType;
use App\Events\Tenant\MessageCreated;
use App\Events\Tenant\MessageCreatedRealtime;
use App\Jobs\Tenant\ProcessExpoPushReceipts;
use App\Models\Tenant\ConversationParticipant;
use App\Models\Tenant\Device;
use App\Models\Tenant\Message;
use App\Models\Tenant\Student;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HandleMessageCreated implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(MessageCreated $event): void
    {
        $initializedByListener = $this->initializeTenantIfNeeded($event->tenantId);

        try {
            $message = Message::query()
                ->with('conversation')
                ->find($event->messageId);

            if (! $message || ! $message->conversation) {
                Log::warning('MessageCreated received without a valid message or conversation', [
                    'message_id' => $event->messageId,
                    'tenant_id' => $event->tenantId,
                ]);

                return;
            }

            $payload = $this->buildTriggerPayload($message);
            $this->broadcastRealtime($message->conversation->uuid, $payload);

            $tenantId = $event->tenantId ?: (tenancy()->initialized ? (string) tenancy()->tenant?->id : '');
            if ($tenantId === '') {
                Log::warning('MessageCreated skip push: tenant context is missing', [
                    'message_id' => $message->id,
                ]);

                return;
            }

            $devices = $this->resolveRecipientDevices($message, $tenantId);
            if ($devices->isEmpty()) {
                return;
            }

            $pendingReceipts = $this->sendPushNotifications(
                devices: $devices,
                senderName: $this->resolveSenderName($message),
                messageBody: (string) $message->body,
                payload: $payload
            );

            if ($pendingReceipts !== []) {
                ProcessExpoPushReceipts::dispatch($tenantId, $pendingReceipts)->delay(now()->addMinutes(2));
            }
        } finally {
            if ($initializedByListener) {
                tenancy()->end();
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTriggerPayload(Message $message): array
    {
        $senderType = $this->resolveSenderType($message);

        return [
            'type' => 'message.new',
            'conversation_uuid' => (string) $message->conversation?->uuid,
            'message_id' => (int) $message->id,
            'sender_type' => $this->mapSenderTypeForClient($senderType),
            'sent_at' => $message->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }

    private function broadcastRealtime(string $conversationUuid, array $payload): void
    {
        if ($conversationUuid === '') {
            return;
        }

        if (! config()->has('broadcasting.connections')) {
            return;
        }

        event(new MessageCreatedRealtime($conversationUuid, $payload));
    }

    private function resolveRecipientDevices(Message $message, string $tenantId): Collection
    {
        $senderType = $this->resolveSenderType($message);

        $recipients = ConversationParticipant::query()
            ->where('conversation_id', $message->conversation_id)
            ->where('participant_type', '!=', $senderType->value)
            ->whereNull('muted_at')
            ->get();

        if ($recipients->isEmpty()) {
            return collect();
        }

        $devices = collect();

        $studentRecipientIds = $recipients
            ->filter(fn (ConversationParticipant $participant) => $participant->participant_type === ParticipantType::STUDENT)
            ->pluck('participant_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($studentRecipientIds->isNotEmpty()) {
            $studentUserIds = Student::query()
                ->whereIn('id', $studentRecipientIds->all())
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            if ($studentUserIds->isNotEmpty()) {
                $devices = $devices->merge(
                    Device::query()
                        ->forTenant($tenantId)
                        ->active()
                        ->whereIn('user_id', $studentUserIds->all())
                        ->get()
                );
            }
        }

        $hasTenantRecipient = $recipients
            ->contains(fn (ConversationParticipant $participant) => $participant->participant_type === ParticipantType::TENANT);

        if ($hasTenantRecipient) {
            $devices = $devices->merge(
                Device::query()
                    ->forTenant($tenantId)
                    ->active()
                    ->whereNotExists(function ($query) {
                        $query->selectRaw('1')
                            ->from('students')
                            ->whereColumn('students.user_id', 'devices.user_id');
                    })
                    ->get()
            );
        }

        return $devices
            ->filter(fn (Device $device) => is_string($device->expo_push_token) && $device->expo_push_token !== '')
            ->unique('expo_push_token')
            ->values();
    }

    private function resolveSenderName(Message $message): string
    {
        $senderType = $this->resolveSenderType($message);

        if ($senderType === ParticipantType::STUDENT) {
            $student = Student::query()->find((int) $message->sender_id);

            return (string) ($student?->full_name ?: 'Alumno');
        }

        $tenantUser = User::query()->find((int) $message->sender_id);

        return (string) ($tenantUser?->name ?: 'Entrenador');
    }

    /**
     * @param  Collection<int, Device>  $devices
     * @param  array<string, mixed>  $payload
     * @return array<string, int>
     */
    private function sendPushNotifications(Collection $devices, string $senderName, string $messageBody, array $payload): array
    {
        if (! config('services.expo.enabled', false)) {
            return [];
        }

        $sendUrl = (string) config('services.expo.send_url', 'https://exp.host/--/api/v2/push/send');
        $pendingReceipts = [];

        foreach ($devices->chunk(100) as $deviceChunk) {
            $chunk = $deviceChunk->values();

            $messages = $chunk
                ->map(function (Device $device) use ($senderName, $messageBody, $payload) {
                    return [
                        'to' => $device->expo_push_token,
                        'title' => $senderName,
                        'body' => Str::limit($messageBody, 120),
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

                continue;
            }

            $tickets = $response->json('data');
            if (! is_array($tickets)) {
                Log::warning('Expo push send returned an unexpected payload', [
                    'body' => $response->body(),
                ]);

                continue;
            }

            foreach ($tickets as $index => $ticket) {
                $device = $chunk->get($index);

                if (! $device instanceof Device || ! is_array($ticket)) {
                    continue;
                }

                $status = (string) data_get($ticket, 'status');

                if ($status === 'ok') {
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

                $errorCode = (string) data_get($ticket, 'details.error', data_get($ticket, 'message', 'unknown'));
                $this->markDeviceError($device, $errorCode);
            }
        }

        return $pendingReceipts;
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

    private function resolveSenderType(Message $message): ParticipantType
    {
        if ($message->sender_type instanceof ParticipantType) {
            return $message->sender_type;
        }

        return ParticipantType::from((string) $message->sender_type);
    }

    private function mapSenderTypeForClient(ParticipantType $senderType): string
    {
        return match ($senderType) {
            ParticipantType::TENANT => 'trainer',
            default => $senderType->value,
        };
    }

    private function initializeTenantIfNeeded(?string $tenantId): bool
    {
        if (! is_string($tenantId) || trim($tenantId) === '') {
            return false;
        }

        try {
            $currentTenantId = tenancy()->initialized ? (string) tenancy()->tenant?->id : null;
            if ($currentTenantId === $tenantId) {
                return false;
            }

            tenancy()->initialize($tenantId);

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Could not initialize tenant in HandleMessageCreated listener', [
                'tenant_id' => $tenantId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
