<?php

namespace App\Livewire\Tenant\Landing;

use App\Models\Configuration;
use App\Models\LandingBooklet;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.tenant')]
class Booklets extends Component
{
    use WithFileUploads;
    public array $booklets = [];
    public $landing_booklet_title = null;

    public $booklet_uuid = null;
    public $booklet_text = '';
    public $booklet_link = '';
    public $booklet_target = '_self';
    public $booklet_active = true;
    public $booklet_order = 0;
    public $booklet_image = null;
    public $edit_mode = false;
    public string $title = '';
    public string $subtitle = '';
    public bool $show = false;



    public function mount(): void
    {
        $tenant = tenant();
        $this->title = Configuration::conf('landing_booklets_title', '');
        $this->subtitle = Configuration::conf('landing_booklets_subtitle', '');
        $this->show = Configuration::conf('landing_booklets_show', false);

        $this->landing_booklet_title = Configuration::conf('landing_booklet_title', '');
        $this->booklets = LandingBooklet::orderBy('order')->get()->map(function ($booklet) {
            return [
                'uuid'      => $booklet->uuid,
                'text'      => $booklet->text,
                'link'      => $booklet->link,
                'target'    => $booklet->target,
                'active'    => $booklet->active,
                'image'     => $booklet->getFirstMediaUrl('cover', 'thumb'),
                'order'     => $booklet->order,
                'to_delete' => false,
            ];
        })->toArray();
        $this->booklet_order = count($this->booklets);
    }

    public function newBooklet()
    {
        $this->reset(['booklet_uuid', 'booklet_text', 'booklet_link', 'booklet_target', 'booklet_order', 'booklet_image', 'booklet_active']);
        $this->edit_mode = false;
    }

    public function editBooklet($uuid)
    {
        $booklet = collect($this->booklets)->firstWhere('uuid', $uuid);
        if ($booklet) {
            $this->booklet_uuid = $booklet['uuid'];
            $this->booklet_text = $booklet['text'];
            $this->booklet_link = $booklet['link'];
            $this->booklet_target = $booklet['target'];
            $this->booklet_order = $booklet['order'];
            $this->booklet_active = $booklet['active'] ? true : false;
            $this->edit_mode = true;
        }
    }

    public function saveBooklet()
    {


        $rules = [
            'booklet_text'   => 'nullable|string|max:255',
            'booklet_link'   => 'nullable|url',
            'booklet_target' => 'required|in:_self,_blank',
            'booklet_order'  => 'nullable|integer|min:0',
            'booklet_active' => 'boolean',
        ];

        if (!$this->edit_mode) {
            $rules['booklet_image'] = 'required|image|mimes:jpg,jpeg,png,webp|max:10048';
        } else {
            $rules['booklet_image'] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:20000';
        }

        $this->validate($rules, [], [
            'booklet_text'   => __('tenant.landing.booklets.text'),
            'booklet_link'   => __('tenant.landing.booklets.link'),
            'booklet_target' => __('tenant.landing.booklets.target'),
            'booklet_order'  => __('tenant.landing.booklets.order'),
            'booklet_active' => __('tenant.landing.booklets.active'),
            'booklet_image'  => __('tenant.landing.booklets.image'),
        ]);




        if ($this->edit_mode && $this->booklet_uuid) {
            $booklet = LandingBooklet::where('uuid', $this->booklet_uuid)->first();
            if ($booklet) {
                $booklet->update([
                    'text'   => $this->booklet_text,
                    'link'   => $this->booklet_link,
                    'target' => $this->booklet_target,
                    'order'  => $this->booklet_order,
                    'active' => $this->booklet_active,
                ]);
                // Imagen
                if ($this->booklet_image) {
                    $booklet->clearMediaCollection('cover');
                    $booklet->addMedia($this->booklet_image)->toMediaCollection('cover');
                }
            }
        } else {
            // Nueva booklet
            $uuid = Str::uuid()->toString();
            $booklet = LandingBooklet::create([
                'uuid'   => $uuid,
                'text'   => $this->booklet_text,
                'link'   => $this->booklet_link,
                'target' => $this->booklet_target,
                'order'  => $this->booklet_order,
                'active' => $this->booklet_active,
            ]);
            if ($this->booklet_image) {
                $booklet->addMedia($this->booklet_image)->toMediaCollection('cover');
            }
        }

        // Refrescar listado
        $this->booklets = LandingBooklet::orderBy('order')->get()->map(function ($booklet) {
            return [
                'uuid'      => $booklet->uuid,
                'text'      => $booklet->text,
                'link'      => $booklet->link,
                'target'    => $booklet->target,
                'image'     => $booklet->getFirstMediaUrl('cover', 'thumb'),
                'order'     => $booklet->order,
                'active'     => $booklet->active,
                'to_delete' => false,
            ];
        })->toArray();
        $this->reorderBooklets();

        $this->newBooklet(); // Resetea el form
        $this->reset('booklet_image');
        $this->booklet_order = count($this->booklets);
        //$this->save();

    }



    public function deleteBooklet($uuid)
    {
        $index = array_search($uuid, array_column($this->booklets, 'uuid'));
        if ($index === false) return;

        // Detectar si ya existe en DB o no (usando campo to_delete para no hacer queries extras)
        $in_db = LandingBooklet::where('uuid', $uuid)->exists();

        if ($in_db) {
            // Solo marcar como to_delete, NO eliminar del array
            $this->booklets[$index]['to_delete'] = true;
        } else {
            // Nueva, la eliminás del array
            array_splice($this->booklets, $index, 1);
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
            'title' => 'nullable|string|max:50',
            'subtitle' => 'nullable|string|max:50',
            'show' => 'boolean',
        ]);
        $show =  $this->show ? true : false;
        Configuration::setConf('landing_booklets_title', $this->title);
        Configuration::setConf('landing_booklets_subtitle', $this->subtitle);
        Configuration::setConf('landing_booklets_show', $show);

        foreach ($this->booklets as $booklet) {
            if (!empty($booklet['to_delete']) && $booklet['to_delete']) {
                $bookletModel = LandingBooklet::where('uuid', $booklet['uuid'])->first();
                if ($bookletModel) {
                    $bookletModel->clearMediaCollection('cover');
                    $bookletModel->delete();
                }
                continue;
            }



            $dbBooklet = LandingBooklet::updateOrCreate(
                ['uuid' => $booklet['uuid']],
                [
                    'text'   => $booklet['text'],
                    'link'   => $booklet['link'],
                    'target' => $booklet['target'],
                    'order'  => $booklet['order'],
                    'active'  => $booklet['active'],
                ]
            );
        }

        $this->dispatch('updated');
        $this->mount();
    }

    public function restoreBooklet($uuid)
    {
        $index = array_search($uuid, array_column($this->booklets, 'uuid'));
        if ($index !== false) {
            $this->booklets[$index]['to_delete'] = false;
        }
    }

    public function moveBookletUp($uuid)
    {
        $booklets = LandingBooklet::orderBy('order')->get();

        $index = $booklets->search(fn($booklet) => $booklet->uuid === $uuid);
        if ($index === false || $index === 0) return; // Ya está arriba del todo

        $current = $booklets[$index];
        $above = $booklets[$index - 1];

        // Swap orden
        $temp = $current->order;
        $current->order = $above->order;
        $above->order = $temp;

        $current->save();
        $above->save();

        $this->mount(); // Refresca el array
    }

    public function moveBookletDown($uuid)
    {
        $booklets = LandingBooklet::orderBy('order')->get();

        $index = $booklets->search(fn($booklet) => $booklet->uuid === $uuid);
        if ($index === false || $index === $booklets->count() - 1) return; // Ya está al final

        $current = $booklets[$index];
        $below = $booklets[$index + 1];

        // Swap orden
        $temp = $current->order;
        $current->order = $below->order;
        $below->order = $temp;

        $current->save();
        $below->save();

        $this->mount(); // Refresca el array
    }

    private function reorderBooklets(): void
    {
        $booklets = LandingBooklet::whereNull('deleted_at')->orderBy('order')->get();

        foreach ($booklets as $index => $booklet) {
            if ($booklet->order !== $index) {
                $booklet->update(['order' => $index]);
            }
        }

        $this->mount(); // Actualiza el listado en memoria también
    }


    public function render()
    {
        return view('livewire.tenant.landing.booklets');
    }
}
