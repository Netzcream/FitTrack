<?php

namespace App\Livewire\Tenant\Configuration;

use Livewire\Component;
use App\Models\Configuration;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.tenant')]
class General extends Component
{
    public string $name = '';
    public string $whatsapp = '';

    public function mount(): void
    {
        $this->name = tenant()->name;
        $this->whatsapp = Configuration::conf('landing_whatsapp', '');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        tenant()->update(['name' => $validated['name']]);
        Configuration::setConf('landing_whatsapp', $this->whatsapp);

        $this->dispatch('updated', name: tenant()->name);
    }

    public function render()
    {
        return view('livewire.tenant.configuration.general');
    }
}
