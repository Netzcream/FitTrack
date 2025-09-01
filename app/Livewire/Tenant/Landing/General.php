<?php

namespace App\Livewire\Tenant\Landing;

use App\Models\Configuration;
use App\Models\LandingCard;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.tenant')]
class General extends Component
{
    use WithFileUploads;


    public string $title = '';
    public string $subtitle = '';
    public string $description = '';
    public string $footer = '';
    public string $show_form = '';
    public string $footerText = '';
    public string $footerBackground = '';

    public $cover;
    public $coverUrl;




    public function mount(): void
    {
        $tenant = tenant();

        $this->title = Configuration::conf('landing_title', '');
        $this->subtitle = Configuration::conf('landing_subtitle', '');
        $this->description = Configuration::conf('landing_description', '');
        $this->footer = Configuration::conf('landing_footer', '');
        $this->footerText = Configuration::conf('landing_footer_text_color', '#6a7282');
        $this->footerBackground = Configuration::conf('landing_footer_background_color', '#333333');
        $this->coverUrl = $tenant->config?->getFirstMediaUrl('cover');
        $this->show_form = Configuration::conf('landing_general_show_form', false);

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
        // Guardar campos generales
        $this->validate([
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'show_form' => 'boolean',
        ]);

        $tenant = tenant();

        if ($this->cover) {
            $tenant->config?->clearMediaCollection('cover');
            $tenant->config?->addMedia($this->cover)
                ->toMediaCollection('cover');
            $this->cover = null;
            $this->coverUrl = null;
        }
         $show_form =  $this->show_form ? true : false;

        Configuration::setConf('landing_title', $this->title);
        Configuration::setConf('landing_subtitle', $this->subtitle);
        Configuration::setConf('landing_description', $this->description);
        Configuration::setConf('landing_footer', $this->footer);
        Configuration::setConf('landing_general_show_form', $this->show_form);
        Configuration::setConf('landing_footer_text_color', $this->footerText);
        Configuration::setConf('landing_footer_background_color', $this->footerBackground);



        $this->dispatch('updated');
        $this->mount();
    }


    public function render()
    {
        return view('livewire.tenant.landing.general');
    }
}
