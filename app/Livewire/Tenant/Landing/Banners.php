<?php

namespace App\Livewire\Tenant\Landing;

use App\Models\Configuration;
use App\Models\LandingBanner;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.tenant')]
class Banners extends Component
{
    use WithFileUploads;
    public array $banners = [];
    public $banner_uuid = null;
    public $banner_text = '';
    public $banner_link = '';
    public $banner_target = '_self';
    public $banner_active = true;
    public $banner_order = 0;
    public $banner_image = null;
    public $banner_image_mobile = null;
    public $edit_mode = false;
    public string $show;

    public function mount(): void
    {
        $tenant = tenant();
        $this->show = Configuration::conf('landing_banners_show') ? true : false;

        $this->banners = LandingBanner::orderBy('order')->get()->map(function ($banner) {
            return [
                'uuid'      => $banner->uuid,
                'text'      => $banner->text,
                'link'      => $banner->link,
                'target'    => $banner->target,
                'active'    => $banner->active,
                'image'     => $banner->getFirstMediaUrl('cover', 'thumb'),
                'order'     => $banner->order,
                'to_delete' => false,
            ];
        })->toArray();
    }

    public function newBanner()
    {
        $this->reset(['banner_uuid', 'banner_text', 'banner_link', 'banner_target', 'banner_order', 'banner_image', 'banner_active']);
        $this->edit_mode = false;
    }

    public function editBanner($uuid)
    {
        $banner = collect($this->banners)->firstWhere('uuid', $uuid);
        if ($banner) {
            $this->banner_uuid = $banner['uuid'];
            $this->banner_text = $banner['text'];
            $this->banner_link = $banner['link'];
            $this->banner_target = $banner['target'];
            $this->banner_order = $banner['order'];
            $this->banner_active = $banner['active'] ? true : false;
            $this->edit_mode = true;
        }
    }

    public function saveBanner()
    {

        $rules = [
            'banner_text'   => 'nullable|string|max:255',
            'banner_link'   => 'nullable|url',
            'banner_target' => 'required|in:_self,_blank',
            'banner_order'  => 'nullable|integer|min:0',
            'banner_active' => 'boolean',
        ];

        if (!$this->edit_mode) {
            $rules['banner_image'] = 'required|image|mimes:jpg,jpeg,png,webp|max:20000';
            $rules['banner_image_mobile'] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:20000';
        } else {
            $rules['banner_image'] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:20000';
            $rules['banner_image_mobile'] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:20000';
        }

        $this->validate($rules, [], [
            'banner_text'   => __('tenant.landing.banners.text'),
            'banner_link'   => __('tenant.landing.banners.link'),
            'banner_target' => __('tenant.landing.banners.target'),
            'banner_order'  => __('tenant.landing.banners.order'),
            'banner_active' => __('tenant.landing.banners.active'),
            'banner_image'  => __('tenant.landing.banners.image'),
        ]);


        if ($this->edit_mode && $this->banner_uuid) {
            $banner = LandingBanner::where('uuid', $this->banner_uuid)->first();
            if ($banner) {
                $banner->update([
                    'text'   => $this->banner_text,
                    'link'   => $this->banner_link,
                    'target' => $this->banner_target,
                    'order'  => $this->banner_order,
                    'active' => $this->banner_active,
                ]);
                // Imagen
                if ($this->banner_image) {
                    $banner->clearMediaCollection('cover');
                    $banner->addMedia($this->banner_image)->toMediaCollection('cover');
                }
                if ($this->banner_image_mobile) {
                    $banner->clearMediaCollection('cover_mobile');
                    $banner->addMedia($this->banner_image_mobile)->toMediaCollection('cover_mobile');
                }
            }
        } else {
            // Nuevo Banner
            $uuid = Str::uuid()->toString();
            $banner = LandingBanner::create([
                'uuid'   => $uuid,
                'text'   => $this->banner_text,
                'link'   => $this->banner_link,
                'target' => $this->banner_target,
                'order'  => $this->banner_order,
                'active' => $this->banner_active,
            ]);
            if ($this->banner_image) {
                $banner->addMedia($this->banner_image)->toMediaCollection('cover');
            }
            if ($this->banner_image_mobile) {
                $banner->addMedia($this->banner_image_mobile)->toMediaCollection('cover_mobile');
            }
        }

        // Refrescar listado
        $this->banners = LandingBanner::orderBy('order')->get()->map(function ($banner) {
            return [
                'uuid'      => $banner->uuid,
                'text'      => $banner->text,
                'link'      => $banner->link,
                'target'    => $banner->target,
                'image'     => $banner->getFirstMediaUrl('cover', 'thumb'),
                'order'     => $banner->order,
                'active'     => $banner->active,
                'to_delete' => false,
            ];
        })->toArray();
        $this->reorderBanners();

        $this->newBanner(); // Resetea el form
                $this->reset('banner_image', 'banner_image_mobile');
    }



    public function deleteBanner($uuid)
    {
        $index = array_search($uuid, array_column($this->banners, 'uuid'));
        if ($index === false) return;

        // Detectar si ya existe en DB o no (usando campo to_delete para no hacer queries extras)
        $in_db = LandingBanner::where('uuid', $uuid)->exists();

        if ($in_db) {
            // Solo marcar como to_delete, NO eliminar del array
            $this->banners[$index]['to_delete'] = true;
        } else {
            // Nueva, la eliminás del array
            array_splice($this->banners, $index, 1);
        }
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
            'show' => 'boolean',
        ]);
        $show =  $this->show ? true : false;
        Configuration::setConf('landing_banners_show', $show);

        foreach ($this->banners as $banner) {
            if (!empty($banner['to_delete']) && $banner['to_delete']) {
                $bannerModel = LandingBanner::where('uuid', $banner['uuid'])->first();
                if ($bannerModel) {
                    $bannerModel->clearMediaCollection('cover');
                    $bannerModel->delete();
                }
                continue;
            }



            LandingBanner::updateOrCreate(
                ['uuid' => $banner['uuid']],
                [
                    'text'   => $banner['text'],
                    'link'   => $banner['link'],
                    'target' => $banner['target'],
                    'order'  => $banner['order'],
                    'active'  => $banner['active'],
                ]
            );
        }

        $this->dispatch('updated');
        $this->mount();
    }

    public function restoreBanner($uuid)
    {
        $index = array_search($uuid, array_column($this->banners, 'uuid'));
        if ($index !== false) {
            $this->banners[$index]['to_delete'] = false;
        }
    }

    public function moveBannerUp($uuid)
    {
        $banners = LandingBanner::orderBy('order')->get();

        $index = $banners->search(fn($banner) => $banner->uuid === $uuid);
        if ($index === false || $index === 0) return; // Ya está arriba del todo

        $current = $banners[$index];
        $above = $banners[$index - 1];

        // Swap orden
        $temp = $current->order;
        $current->order = $above->order;
        $above->order = $temp;

        $current->save();
        $above->save();

        $this->mount(); // Refresca el array
    }

    public function moveBannerDown($uuid)
    {
        $banners = LandingBanner::orderBy('order')->get();

        $index = $banners->search(fn($banner) => $banner->uuid === $uuid);
        if ($index === false || $index === $banners->count() - 1) return; // Ya está al final

        $current = $banners[$index];
        $below = $banners[$index + 1];

        // Swap orden
        $temp = $current->order;
        $current->order = $below->order;
        $below->order = $temp;

        $current->save();
        $below->save();

        $this->mount(); // Refresca el array
    }

    private function reorderBanners(): void
    {
        $banners = LandingBanner::whereNull('deleted_at')->orderBy('order')->get();

        foreach ($banners as $index => $banner) {
            if ($banner->order !== $index) {
                $banner->update(['order' => $index]);
            }
        }

        $this->mount(); // Actualiza el listado en memoria también
    }


    public function render()
    {
        return view('livewire.tenant.landing.banners');
    }
}
