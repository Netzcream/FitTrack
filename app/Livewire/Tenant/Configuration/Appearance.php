<?php

namespace App\Livewire\Tenant\Configuration;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Configuration;

#[Layout('components.layouts.tenant')]
class Appearance extends Component
{
    use WithFileUploads;

    public $logo;
    public $favicon;

    public $logoUrl;
    public $faviconUrl;

    public $color_base;
    public $color_dark;
    public $color_light;

    public function mount(): void
    {
        $tenant = tenant();

        $this->logoUrl = $tenant->config?->getFirstMediaUrl('logo');
        $this->faviconUrl = $tenant->config?->getFirstMediaUrl('favicon');

        $this->color_base = Configuration::conf('color_base', '#263d83');
        $this->color_dark = Configuration::conf('color_dark', '#3b4f9e');
        $this->color_light = Configuration::conf('color_light', '#fafafa');
    }

    public function removeMedia(string $collection): void
    {
        tenant()->config?->clearMediaCollection($collection);
        $this->{$collection . 'Url'} = null;
        $this->dispatch('updated');
    }

    public function removePreview(string $collection): void
    {
        $this->reset($collection);
    }

    public function save(): void
    {
        $this->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,webp,ico|max:512',
            'color_base' => 'nullable|string|max:20',
            'color_dark' => 'nullable|string|max:20',
            'color_light' => 'nullable|string|max:20',
        ]);

        $tenant = tenant();

        if ($this->logo) {
            $tenant->config->clearMediaCollection('logo');
            $tenant->config->addMedia($this->logo)->toMediaCollection('logo');
            $this->logo = null;
            $this->logoUrl = null;
        }

        if ($this->favicon) {
            $tenant->config?->clearMediaCollection('favicon');
            $tenant->config?->addMedia($this->favicon)->toMediaCollection('favicon');
            $this->favicon = null;
            $this->faviconUrl = null;
        }

        $base = $this->color_base = strtolower($this->color_base);
         ltrim($this->color_base, '#');

        $dark = strtolower($this->color_dark);


        $light = strtolower($this->color_light);




        Configuration::setConf('color_base', $this->color_base);
        Configuration::setConf('color_dark', $dark);
        Configuration::setConf('color_light', $light);

        $this->mount();
        $this->dispatch('updated');
    }

    public function render()
    {
        return view('livewire.tenant.configuration.appearance');
    }
}
