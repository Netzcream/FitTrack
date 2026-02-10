<?php

namespace App\Livewire\Tenant\Configuration;

use App\Jobs\Tenant\ProcessExpoPushReceipts;
use App\Jobs\Tenant\SendTestTenantNotificationMail;
use App\Models\Configuration;
use App\Models\Tenant\Device;
use App\Services\Tenant\ExpoPushService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.tenant')]
class Notification extends Component
{
    public string $contact_email = '';

    public string $push_target = 'all';

    public string $push_device_id = '';

    public string $push_title = '';

    public string $push_message = '';

    public array $push_devices = [];

    public int $active_devices_count = 0;

    public ?string $push_feedback = null;

    public bool $push_feedback_is_error = false;

    public function mount(): void
    {
        $this->contact_email = Configuration::conf('contact_email', 'services@fittrack.com.ar');
        $this->push_title = (string) (tenant()?->name ?: 'FitTrack');
        $this->loadPushDevices();
    }

    public function testContactEmail(): void
    {
        $this->validate([
            'contact_email' => ['required', 'email', 'max:255'],
        ]);

        SendTestTenantNotificationMail::dispatch(
            channel: 'contactos',
            targetEmail: $this->contact_email,
            reason: 'Verificación de configuración de correo de notificación de contactos'
        );

        $this->dispatch('tested', channel: 'contactos', to: $this->contact_email);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'contact_email' => ['required', 'email', 'max:255'],
        ]);

        Configuration::setConf('contact_email', $validated['contact_email']);

        $this->dispatch('updated', email: $validated['contact_email']);
    }

    public function updatedPushTarget(string $value): void
    {
        if ($value !== 'device') {
            $this->push_device_id = '';
        }
    }

    public function sendPushNotification(): void
    {
        $this->resetErrorBag(['push_target', 'push_device_id', 'push_title', 'push_message', 'push_send']);
        $this->push_feedback = null;
        $this->push_feedback_is_error = false;

        $rules = [
            'push_target' => ['required', 'in:all,device'],
            'push_title' => ['required', 'string', 'max:80'],
            'push_message' => ['required', 'string', 'max:120'],
        ];

        if ($this->push_target === 'device') {
            $rules['push_device_id'] = ['required', 'integer'];
        }

        $this->validate($rules);

        $tenantId = tenancy()->initialized ? (string) tenancy()->tenant?->id : '';
        if ($tenantId === '') {
            $this->addError('push_send', __('tenant.configuration.notification.push.tenant_not_available'));

            return;
        }

        $devicesQuery = Device::query()
            ->forTenant($tenantId)
            ->active();

        if ($this->push_target === 'device') {
            $devicesQuery->whereKey((int) $this->push_device_id);
        }

        $devices = $devicesQuery->get();
        if ($devices->isEmpty()) {
            $this->addError('push_send', __('tenant.configuration.notification.push.device_not_found'));
            $this->loadPushDevices();

            return;
        }

        $result = app(ExpoPushService::class)->send(
            devices: $devices,
            title: $this->push_title,
            body: $this->push_message,
            payload: [
                'type' => 'admin.push',
                'sent_at' => now()->toIso8601String(),
            ],
        );

        if (($result['disabled'] ?? false) === true) {
            $this->addError('push_send', __('tenant.configuration.notification.push.disabled'));

            return;
        }

        $pendingReceipts = $result['pending_receipts'] ?? [];
        if (is_array($pendingReceipts) && $pendingReceipts !== []) {
            ProcessExpoPushReceipts::dispatch($tenantId, $pendingReceipts)->delay(now()->addMinutes(2));
        }

        $sentCount = (int) ($result['sent_count'] ?? 0);
        $errorCount = (int) ($result['error_count'] ?? 0);

        if ($sentCount > 0 && $errorCount === 0) {
            $this->push_feedback = __('tenant.configuration.notification.push.sent_success', ['sent' => $sentCount]);
            $this->push_feedback_is_error = false;
            $this->push_message = '';
        } elseif ($sentCount > 0) {
            $this->push_feedback = __('tenant.configuration.notification.push.sent_partial', [
                'sent' => $sentCount,
                'errors' => $errorCount,
            ]);
            $this->push_feedback_is_error = false;
            $this->push_message = '';
        } else {
            $this->push_feedback = __('tenant.configuration.notification.push.send_failed');
            $this->push_feedback_is_error = true;
        }

        $this->loadPushDevices();
    }

    private function loadPushDevices(): void
    {
        $tenantId = tenancy()->initialized ? (string) tenancy()->tenant?->id : '';
        if ($tenantId === '') {
            $this->push_devices = [];
            $this->active_devices_count = 0;

            return;
        }

        $devices = Device::query()
            ->forTenant($tenantId)
            ->active()
            ->with('user:id,name,email')
            ->orderByDesc('last_seen_at')
            ->orderByDesc('id')
            ->get();

        $this->active_devices_count = $devices->count();
        $this->push_devices = $devices
            ->map(fn (Device $device): array => [
                'id' => (int) $device->id,
                'label' => $this->formatPushDeviceLabel($device),
            ])
            ->values()
            ->all();
    }

    private function formatPushDeviceLabel(Device $device): string
    {
        $owner = trim((string) ($device->user?->name ?: $device->user?->email ?: ('User #'.$device->user_id)));
        $platform = strtoupper((string) $device->platform);
        $lastSeen = $device->last_seen_at?->format('d/m H:i') ?? 'n/a';
        $tokenSuffix = substr((string) $device->expo_push_token, -8);

        return sprintf('%s | %s | %s | ...%s', $owner, $platform, $lastSeen, $tokenSuffix);
    }

    public function render()
    {
        return view('livewire.tenant.configuration.notification');
    }
}
