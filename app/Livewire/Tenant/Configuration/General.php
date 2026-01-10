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

    // Métodos de pago aceptados
    public bool $accepts_transfer = false;
    public string $bank_name = '';
    public string $bank_account_holder = '';
    public string $bank_cuit_cuil = '';
    public string $bank_cbu = '';
    public string $bank_alias = '';
    public string $transfer_instructions = '';

    public bool $accepts_mercadopago = false;
    public string $mp_access_token = '';
    public string $mp_public_key = '';
    public string $mp_instructions = '';

    public bool $accepts_cash = false;
    public string $cash_instructions = '';

    public function mount(): void
    {
        $this->name = tenant()->name;
        $this->whatsapp = Configuration::conf('landing_whatsapp', '');
        $this->instagram = Configuration::conf('landing_instagram', '');
        $this->facebook = Configuration::conf('landing_facebook', '');
        $this->youtube = Configuration::conf('landing_youtube', '');
        $this->twitter = Configuration::conf('landing_twitter', '');
        $this->tiktok = Configuration::conf('landing_tiktok', '');

        // Métodos de pago
        $this->accepts_transfer = (bool) Configuration::conf('payment_accepts_transfer', false);
        $this->bank_name = Configuration::conf('payment_bank_name', '');
        $this->bank_account_holder = Configuration::conf('payment_bank_account_holder', '');
        $this->bank_cuit_cuil = Configuration::conf('payment_bank_cuit_cuil', '');
        $this->bank_cbu = Configuration::conf('payment_bank_cbu', '');
        $this->bank_alias = Configuration::conf('payment_bank_alias', '');
        $this->transfer_instructions = Configuration::conf('payment_transfer_instructions', '');

        $this->accepts_mercadopago = (bool) Configuration::conf('payment_accepts_mercadopago', false);
        $this->mp_access_token = Configuration::conf('payment_mp_access_token', '');
        $this->mp_public_key = Configuration::conf('payment_mp_public_key', '');
        $this->mp_instructions = Configuration::conf('payment_mp_instructions', '');

        $this->accepts_cash = (bool) Configuration::conf('payment_accepts_cash', false);
        $this->cash_instructions = Configuration::conf('payment_cash_instructions', '');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'facebook' => ['nullable', 'string', 'max:100'],
            'youtube' => ['nullable', 'string', 'max:100'],
            'twitter' => ['nullable', 'string', 'max:100'],
            'tiktok' => ['nullable', 'string', 'max:100'],

            // Validaciones de métodos de pago
            'accepts_transfer' => ['boolean'],
            'bank_name' => ['nullable', 'string', 'max:200'],
            'bank_account_holder' => ['nullable', 'string', 'max:200'],
            'bank_cuit_cuil' => ['nullable', 'string', 'max:20'],
            'bank_cbu' => ['nullable', 'string', 'max:50'],
            'bank_alias' => ['nullable', 'string', 'max:100'],
            'transfer_instructions' => ['nullable', 'string', 'max:500'],

            'accepts_mercadopago' => ['boolean'],
            'mp_access_token' => ['nullable', 'string', 'max:512'],
            'mp_public_key' => ['nullable', 'string', 'max:512'],
            'mp_instructions' => ['nullable', 'string', 'max:500'],

            'accepts_cash' => ['boolean'],
            'cash_instructions' => ['nullable', 'string', 'max:500'],
        ]);

        tenant()->update(['name' => $validated['name']]);

        // Configuraciones básicas
        Configuration::setConf('landing_whatsapp', $this->whatsapp);
        Configuration::setConf('landing_instagram', $this->instagram);
        Configuration::setConf('landing_facebook', $this->facebook);
        Configuration::setConf('landing_youtube', $this->youtube);
        Configuration::setConf('landing_twitter', $this->twitter);
        Configuration::setConf('landing_tiktok', $this->tiktok);

        // Métodos de pago
        Configuration::setConf('payment_accepts_transfer', $this->accepts_transfer);
        Configuration::setConf('payment_bank_name', $this->bank_name);
        Configuration::setConf('payment_bank_account_holder', $this->bank_account_holder);
        Configuration::setConf('payment_bank_cuit_cuil', $this->bank_cuit_cuil);
        Configuration::setConf('payment_bank_cbu', $this->bank_cbu);
        Configuration::setConf('payment_bank_alias', $this->bank_alias);
        Configuration::setConf('payment_transfer_instructions', $this->transfer_instructions);

        Configuration::setConf('payment_accepts_mercadopago', $this->accepts_mercadopago);
        Configuration::setConf('payment_mp_access_token', $this->mp_access_token);
        Configuration::setConf('payment_mp_public_key', $this->mp_public_key);
        Configuration::setConf('payment_mp_instructions', $this->mp_instructions);

        Configuration::setConf('payment_accepts_cash', $this->accepts_cash);
        Configuration::setConf('payment_cash_instructions', $this->cash_instructions);

        $this->dispatch('updated', name: tenant()->name);
    }

    public function render()
    {
        return view('livewire.tenant.configuration.general');
    }
}
