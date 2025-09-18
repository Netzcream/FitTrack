<?php

namespace App\Livewire\Tenant\CommunicationChannels;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\CommunicationChannel;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $id = null;
    public bool $editMode = false;

    public string $name = '';
    public string $code = '';
    public bool $is_active = true;

    public bool $back = true;

    public function mount(?CommunicationChannel $communicationChannel): void
    {
        if ($communicationChannel && $communicationChannel->exists) {
            $this->editMode  = true;
            $this->id        = (int) $communicationChannel->id;
            $this->name      = (string) $communicationChannel->name;
            $this->code      = (string) $communicationChannel->code;
            $this->is_active = (bool) $communicationChannel->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required', 'string', 'max:100',
                $this->editMode
                    ? Rule::unique('communication_channels', 'code')->ignore($this->id)
                    : Rule::unique('communication_channels', 'code'),
            ],
            'is_active' => ['boolean'],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $channel = $this->editMode
            ? CommunicationChannel::findOrFail($this->id)
            : new CommunicationChannel();

        $channel->fill($validated)->save();

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('site.channel_updated'));
            $this->mount($channel->fresh());
        } else {
            session()->flash('success', __('site.channel_created'));
        }

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.communication-channels.index'), navigate: true);
        }

        return $this->redirect(route('tenant.dashboard.communication-channels.edit', $channel), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.communication-channels.form');
    }
}
