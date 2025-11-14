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
    public string $instagram = '';
    public string $facebook = '';
    public string $youtube = '';
    public string $twitter = '';
    public string $tiktok = '';

    public function mount(): void
    {
        $this->name = tenant()->name;
        $this->whatsapp = Configuration::conf('landing_whatsapp', '');
        $this->instagram = Configuration::conf('landing_instagram', '');
        $this->facebook = Configuration::conf('landing_facebook', '');
        $this->youtube = Configuration::conf('landing_youtube', '');
        $this->twitter = Configuration::conf('landing_twitter', '');
        $this->tiktok = Configuration::conf('landing_tiktok', '');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        tenant()->update(['name' => $validated['name']]);
        Configuration::setConf('landing_whatsapp', $this->whatsapp);
        Configuration::setConf('landing_instagram', $this->instagram);
        Configuration::setConf('landing_facebook', $this->facebook);
        Configuration::setConf('landing_youtube', $this->youtube);
        Configuration::setConf('landing_twitter', $this->twitter);
        Configuration::setConf('landing_tiktok', $this->tiktok);

        $this->dispatch('updated', name: tenant()->name);
    }

    public function render()
    {
        return view('livewire.tenant.configuration.general');
    }
}
