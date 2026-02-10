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
use App\Services\Tenant\ExpoPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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

            $pushResult = app(ExpoPushService::class)->send(
                devices: $devices,
                title: $this->resolveSenderName($message),
                body: (string) $message->body,
                payload: $payload,
            );

            $pendingReceipts = $pushResult['pending_receipts'] ?? [];
            if (is_array($pendingReceipts) && $pendingReceipts !== []) {
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
