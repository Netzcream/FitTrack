<?php

namespace App\Livewire\Tenant\PaymentMethods;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\PaymentMethod;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.tenant')]
class Form extends Component
{
    public ?int $id = null;
    public bool $editMode = false;

    // basics
    public string $name = '';
    public string $code = '';
    public bool $is_active = true;

    // content
    public ?string $description = null;
    public ?string $instructions = null;

    // config (provider/token básicos, extensible)
    public ?string $provider = null;
    public ?string $token = null;

    public bool $back = true;

    public function mount(?PaymentMethod $paymentMethod): void
    {
        if ($paymentMethod && $paymentMethod->exists) {
            $this->editMode    = true;
            $this->id          = (int) $paymentMethod->id;
            $this->name        = (string) $paymentMethod->name;
            $this->code        = (string) $paymentMethod->code;
            $this->is_active   = (bool) $paymentMethod->is_active;
            $this->description = $paymentMethod->description;
            $this->instructions = $paymentMethod->instructions;

            $cfg = $paymentMethod->config ?? [];
            $this->provider = $cfg['provider'] ?? null;
            $this->token    = $cfg['token'] ?? null;
        }
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:120'],
            'code'        => [
                'required', 'string', 'max:100',
                $this->editMode
                    ? Rule::unique('payment_methods', 'code')->ignore($this->id)
                    : Rule::unique('payment_methods', 'code'),
            ],
            'is_active'   => ['boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
            'instructions'=> ['nullable', 'string', 'max:4000'],
            'provider'    => ['nullable', 'string', 'max:120'],
            'token'       => ['nullable', 'string', 'max:512'],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $method = $this->editMode
            ? PaymentMethod::findOrFail($this->id)
            : new PaymentMethod();

        $method->fill([
            'name'        => $validated['name'],
            'code'        => $validated['code'],
            'is_active'   => $validated['is_active'] ?? true,
            'description' => $validated['description'] ?? null,
            'instructions'=> $validated['instructions'] ?? null,
        ]);

        // Guardamos config json extendible
        $method->config = [
            'provider' => $validated['provider'] ?? null,
            'token'    => $validated['token'] ?? null,
        ];

        $method->save();

        if ($this->editMode) {
            $this->dispatch('updated');
            session()->flash('success', __('site.payment_method_updated'));
            $this->mount($method->fresh());
        } else {
            session()->flash('success', __('site.payment_method_created'));
        }

        if ($this->back) {
            return $this->redirect(route('tenant.dashboard.payment-methods.index'), navigate: true);
        }

        return $this->redirect(route('tenant.dashboard.payment-methods.edit', $method), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.payment-methods.form');
    }
}
