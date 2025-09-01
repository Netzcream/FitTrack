<?php

namespace App\Livewire\Tenant\Configuration;

use Livewire\Component;

use Livewire\Attributes\Layout;

#[Layout('components.layouts.tenant')]
class Notification extends Component
{
    public string $contact_email = '';

    public function mount(): void
    {
        $this->contact_email = tenant()->contact_email ?? 'services@fittrack.com.ar';
    }


    public function save(): void
    {
        $validated = $this->validate([
            'contact_email' => ['required', 'email', 'max:255'],
        ]);

        tenant()->contact_email = $validated['contact_email'];

        tenant()->save();
        $this->dispatch('updated', name: tenant()->contact_email);
    }

    public function render()
    {
        return view('livewire.tenant.configuration.notification');
    }
}
