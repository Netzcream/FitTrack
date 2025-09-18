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
class Cards extends Component
{
    use WithFileUploads;
    public array $cards = [];
    public $card_uuid = null;
    public $card_text = '';
    public $card_title = '';

    public $card_link = '';
    public $card_target = '_self';
    public $card_active = true;
    public $card_order = 0;
    public $card_image = null;
    public $edit_mode = false;
    public string $title = '';
    public string $subtitle = '';
    public string $show = '';

    public function mount(): void
    {
        $tenant = tenant();
        $this->title = Configuration::conf('landing_cards_title', '');
        $this->subtitle = Configuration::conf('landing_cards_subtitle', '');
        $this->show = Configuration::conf('landing_cards_show', false);
        $this->cards = LandingCard::orderBy('order')->get()->map(function ($card) {
            return [
                'uuid'      => $card->uuid,
                'text'      => $card->text,
                'title'     => $card->title,
                'link'      => $card->link,
                'target'    => $card->target,
                'active'    => $card->active,
                'image'     => $card->getFirstMediaUrl('cover', 'thumb'),
                'order'     => $card->order,
                'to_delete' => false,
            ];
        })->toArray();
    }

    public function newCard()
    {
        $this->reset(['card_uuid', 'card_text', 'card_title', 'card_link', 'card_target', 'card_order', 'card_image', 'card_active']);
        $this->edit_mode = false;
    }

    public function editCard($uuid)
    {
        $card = collect($this->cards)->firstWhere('uuid', $uuid);
        if ($card) {
            $this->card_uuid = $card['uuid'];
            $this->card_title = $card['title'];
            $this->card_text = $card['text'];
            $this->card_link = $card['link'];
            $this->card_target = $card['target'];
            $this->card_order = $card['order'];
            $this->card_active = $card['active'] ? true : false;
            $this->edit_mode = true;
        }
    }

    public function saveCard()
    {
        $this->validate([
            'card_text'   => 'required|string|max:255',
            'card_title'   => 'required|string|max:50',
            'card_link'   => 'nullable|url',
            'card_target' => 'required|in:_self,_blank',
            'card_order'  => 'nullable|integer|min:0',
            'card_image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'card_active'  => 'boolean',
        ],[], [
            'card_text'   => __('tenant.landing.cards.text'),
            'card_link'   => __('tenant.landing.cards.link'),
            'card_title'  => __('tenant.landing.cards.title'),
            'card_target' => __('tenant.landing.cards.target'),
            'card_order'  => __('tenant.landing.cards.order'),
            'card_active' => __('tenant.landing.cards.active'),
            'card_image'  => __('tenant.landing.cards.image'),
            'card_active' => __('tenant.landing.cards.active'),
        ]);


        if ($this->edit_mode && $this->card_uuid) {
            $card = LandingCard::where('uuid', $this->card_uuid)->first();
            if ($card) {
                $card->update([
                    'text'   => $this->card_text,
                    'title'   => $this->card_title,
                    'link'   => $this->card_link,
                    'target' => $this->card_target,
                    'order'  => $this->card_order,
                    'active' => $this->card_active,
                ]);
                // Imagen
                if ($this->card_image) {
                    $card->clearMediaCollection('cover');
                    $card->addMedia($this->card_image)->toMediaCollection('cover');
                }
            }
        } else {
            // Nueva card
            $uuid = Str::uuid()->toString();
            $card = LandingCard::create([
                'uuid'   => $uuid,
                'text'   => $this->card_text,
                'title'   => $this->card_title,
                'link'   => $this->card_link,
                'target' => $this->card_target,
                'order'  => $this->card_order,
                'active' => $this->card_active,
            ]);
            if ($this->card_image) {
                $card->addMedia($this->card_image)->toMediaCollection('cover');
            }
        }

        // Refrescar listado
        $this->cards = LandingCard::orderBy('order')->get()->map(function ($card) {
            return [
                'uuid'      => $card->uuid,
                'text'      => $card->text,
                'title'      => $card->title,
                'link'      => $card->link,
                'target'    => $card->target,
                'image'     => $card->getFirstMediaUrl('cover', 'thumb'),
                'order'     => $card->order,
                'active'     => $card->active,
                'to_delete' => false,
            ];
        })->toArray();
        $this->reorderCards();

        $this->newCard(); // Resetea el form
        $this->reset('card_image');
        $this->save();
    }



    public function deleteCard($uuid)
    {
        $index = array_search($uuid, array_column($this->cards, 'uuid'));
        if ($index === false) return;

        // Detectar si ya existe en DB o no (usando campo to_delete para no hacer queries extras)
        $in_db = LandingCard::where('uuid', $uuid)->exists();

        if ($in_db) {
            // Solo marcar como to_delete, NO eliminar del array
            $this->cards[$index]['to_delete'] = true;
        } else {
            // Nueva, la eliminás del array
            array_splice($this->cards, $index, 1);
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

        foreach ($this->cards as $card) {
            if (!empty($card['to_delete']) && $card['to_delete']) {
                $cardModel = LandingCard::where('uuid', $card['uuid'])->first();
                if ($cardModel) {
                    $cardModel->clearMediaCollection('cover');
                    $cardModel->delete();
                }
                continue;
            }



            $dbCard = LandingCard::updateOrCreate(
                ['uuid' => $card['uuid']],
                [
                    'text'   => $card['text'],
                    'title'   => $card['title'],
                    'link'   => $card['link'],
                    'target' => $card['target'],
                    'order'  => $card['order'],
                    'active'  => $card['active'],
                ]
            );
        }
        $show =  $this->show ? true : false;
        Configuration::setConf('landing_cards_title', $this->title);
        Configuration::setConf('landing_cards_subtitle', $this->subtitle);
        Configuration::setConf('landing_cards_show', $show);

        $this->dispatch('updated');
        $this->mount();
    }

    public function restoreCard($uuid)
    {
        $index = array_search($uuid, array_column($this->cards, 'uuid'));
        if ($index !== false) {
            $this->cards[$index]['to_delete'] = false;
        }
    }

    public function moveCardUp($uuid)
    {
        $cards = LandingCard::orderBy('order')->get();

        $index = $cards->search(fn($card) => $card->uuid === $uuid);
        if ($index === false || $index === 0) return; // Ya está arriba del todo

        $current = $cards[$index];
        $above = $cards[$index - 1];

        // Swap orden
        $temp = $current->order;
        $current->order = $above->order;
        $above->order = $temp;

        $current->save();
        $above->save();

        $this->mount(); // Refresca el array
    }

    public function moveCardDown($uuid)
    {
        $cards = LandingCard::orderBy('order')->get();

        $index = $cards->search(fn($card) => $card->uuid === $uuid);
        if ($index === false || $index === $cards->count() - 1) return; // Ya está al final

        $current = $cards[$index];
        $below = $cards[$index + 1];

        // Swap orden
        $temp = $current->order;
        $current->order = $below->order;
        $below->order = $temp;

        $current->save();
        $below->save();

        $this->mount(); // Refresca el array
    }

    private function reorderCards(): void
    {
        $cards = LandingCard::whereNull('deleted_at')->orderBy('order')->get();

        foreach ($cards as $index => $card) {
            if ($card->order !== $index) {
                $card->update(['order' => $index]);
            }
        }

        $this->mount(); // Actualiza el listado en memoria también
    }


    public function render()
    {
        return view('livewire.tenant.landing.cards');
    }
}
