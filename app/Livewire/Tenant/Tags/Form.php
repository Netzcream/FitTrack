<?php

namespace App\Livewire\Tenant\Tags;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Tag;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $id = null;
    public bool $editMode = false;

    public string $name = '';
    public string $code = '';
    public ?string $color = null;
    public ?string $description = null;
    public bool $is_active = true;

    public bool $back = true;

    public function mount(?Tag $tag): void
    {
        if ($tag && $tag->exists) {
            $this->editMode    = true;
            $this->id          = (int) $tag->id;
            $this->name        = (string) $tag->name;
            $this->code        = (string) $tag->code;
            $this->color       = $tag->color;
            $this->description = $tag->description;
            $this->is_active   = (bool) $tag->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:120'],
            'code'        => [
                'required', 'string', 'max:100',
                $this->editMode
                    ? Rule::unique('tags', 'code')->ignore($this->id)
                    : Rule::unique('tags', 'code'),
            ],
            'color'       => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active'   => ['boolean'],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $tag = $this->editMode
            ? Tag::findOrFail($this->id)
            : new Tag();

        $tag->fill($validated)->save();

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('site.tag_updated'));
            $this->mount($tag->fresh());
        } else {
            session()->flash('success', __('site.tag_created'));
        }

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.tags.index'), navigate: true);
        }

        return $this->redirect(route('tenant.dashboard.tags.edit', $tag), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.tags.form');
    }
}
