<?php

namespace App\Livewire\Tenant\Configuration;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Jobs\Tenant\SendTestTenantNotificationMail;

#[Layout('components.layouts.tenant')]
class Notification extends Component
{
    public string $contact_email = '';

    public function mount(): void
    {
        $this->contact_email = tenant()->contact_email ?? 'services@fittrack.com.ar';
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

        tenant()->update(['contact_email' => $validated['contact_email']]);

        $this->dispatch('updated', email: tenant()->contact_email);
    }

    public function render()
    {
        return view('livewire.tenant.configuration.notification');
    }
}
