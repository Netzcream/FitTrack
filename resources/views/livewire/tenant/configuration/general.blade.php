{{-- resources/views/livewire/tenant/configuration/general.blade.php --}}
<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6">

            {{-- Header sticky --}}
            <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
                <div class="flex items-center justify-between gap-4 max-w-md">
                    <div>
                        <flux:heading size="xl" level="1">
                            {{ __('tenant.configuration.general.title') }}
                        </flux:heading>
                        <flux:subheading size="lg" class="mb-6">
                            {{ __('tenant.configuration.general.subtitle') }}
                        </flux:subheading>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-tenant.action-message class="me-3" on="updated">
                            {{ __('site.saved') }}
                        </x-tenant.action-message>

                        <flux:button type="submit" size="sm">
                            {{ __('site.save') }}
                        </flux:button>
                    </div>
                </div>
                <flux:separator variant="subtle" class="mt-2" />
            </div>

            {{-- Contenido del formulario --}}
            <div class="max-w-2xl space-y-6 pt-2">

                {{-- Sección: Información Básica --}}
                <div class="space-y-4">
                    <div>
                        <flux:heading size="lg" class="!mb-1">Información Básica</flux:heading>
                        <p class="text-sm text-gray-600">Configurá el nombre de tu gimnasio y datos de contacto principal.</p>
                    </div>
                    <flux:separator variant="subtle" />

                    {{-- Nombre del sitio --}}
                    <flux:field>
                        <flux:label>Nombre del Gimnasio/Centro</flux:label>
                        <flux:input wire:model.defer="name" type="text" required autofocus autocomplete="name" 
                            placeholder="Ej: Fitness Center" />
                        <flux:error name="name" />
                    </flux:field>

                    {{-- WhatsApp --}}
                    <flux:field>
                        <flux:label>WhatsApp de Contacto</flux:label>
                        <flux:input.group>
                            <flux:input.group.prefix>+</flux:input.group.prefix>
                            <flux:input wire:model.defer="whatsapp" placeholder="5491123456789" autocomplete="tel" />
                        </flux:input.group>
                        <flux:description>Para que tus alumnos puedan contactarte. Ej: 5491123456789 (código país + área + número)</flux:description>
                        <flux:error name="whatsapp" />
                    </flux:field>
                </div>

                {{-- Sección de métodos de pago --}}
                <div class="space-y-4 pt-4">
                    <div>
                        <flux:heading size="lg" class="!mb-1">Métodos de Pago</flux:heading>
                        <p class="text-sm text-gray-600">Configurá cómo tus alumnos pueden pagarte. Habilitá los métodos que aceptás.</p>
                    </div>
                    <flux:separator variant="subtle" />

                {{-- Instagram --}}
                <flux:field>
                    <flux:label>{{ __('tenant.configuration.landing.landing_instagram') }}</flux:label>
                    <flux:input.group>
                        <flux:input.group.prefix>instagram.com/</flux:input.group.prefix>
                        <flux:input wire:model.defer="instagram" placeholder="usuario" />
                    </flux:input.group>
                    <small class="text-xs text-gray-500 mt-1 block">Ingresá solo tu usuario, sin @.</small>
                    <flux:error name="instagram" />
                </flux:field>

                {{-- Facebook --}}
                <flux:field>
                    <flux:label>{{ __('tenant.configuration.landing.landing_facebook') }}</flux:label>
                    <flux:input.group>
                        <flux:input.group.prefix>facebook.com/</flux:input.group.prefix>
                        <flux:input wire:model.defer="facebook" placeholder="nombre_pagina" />
                    </flux:input.group>
                    <small class="text-xs text-gray-500 mt-1 block">Ingresá solo el nombre de tu página o perfil.</small>
                    <flux:error name="facebook" />
                </flux:field>

                {{-- YouTube --}}
                <flux:field>
                    <flux:label>{{ __('tenant.configuration.landing.landing_youtube') }}</flux:label>
                    <flux:input.group>
                        <flux:input.group.prefix>youtube.com/@</flux:input.group.prefix>
                        <flux:input wire:model.defer="youtube" placeholder="canal" />
                    </flux:input.group>
                    <small class="text-xs text-gray-500 mt-1 block">Ingresá el nombre de tu canal.</small>
                    <flux:error name="youtube" />
                </flux:field>

                {{-- Twitter/X --}}
                <flux:field>
                    <flux:label>{{ __('tenant.configuration.landing.landing_twitter') }}</flux:label>
                    <flux:input.group>
                        <flux:input.group.prefix>x.com/</flux:input.group.prefix>
                        <flux:input wire:model.defer="twitter" placeholder="usuario" />
                    </flux:input.group>
                    <small class="text-xs text-gray-500 mt-1 block">Ingresá solo tu usuario, sin @.</small>
                    <flux:error name="twitter" />
                </flux:field>

                {{-- TikTok --}}
                <flux:field>
                    <flux:label>{{ __('tenant.configuration.landing.landing_tiktok') }}</flux:label>
                    <flux:input.group>
                        <flux:input.group.prefix>tiktok.com/@</flux:input.group.prefix>
                        <flux:input wire:model.defer="tiktok" placeholder="usuario" />
                    </flux:input.group>
                    <small class="text-xs text-gray-500 mt-1 block">Ingresá solo tu usuario.</small>
                    <flux:error name="tiktok" />
                </flux:field>

                {{-- Sección de métodos de pago --}}
                <flux:heading size="md" class="mt-8">{{ __('tenant.configuration.general.payment_methods_title') }}</flux:heading>
                <flux:separator variant="subtle" class="my-2" />
                <p class="text-sm text-gray-600 mb-4">{{ __('tenant.configuration.general.payment_methods_description') }}</p>

                {{-- Transferencia/Depósito --}}
                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg space-y-3">
                    <flux:checkbox wire:model.defer="accepts_transfer" :label="__('tenant.configuration.general.accepts_transfer')" />

                    <div x-show="$wire.accepts_transfer" class="space-y-3 mt-3">
                        <flux:field>
                            <flux:label>{{ __('tenant.configuration.general.bank_name') }}</flux:label>
                            <flux:input wire:model.defer="bank_name" placeholder="Ej: Banco Galicia" />
                            <flux:error name="bank_name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('tenant.configuration.general.bank_account_holder') }}</flux:label>
                            <flux:input wire:model.defer="bank_account_holder" placeholder="Ej: Juan Pérez" />
                            <flux:error name="bank_account_holder" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('tenant.configuration.general.bank_cuit_cuil') }}</flux:label>
                            <flux:input wire:model.defer="bank_cuit_cuil" placeholder="20-12345678-9" />
                            <flux:error name="bank_cuit_cuil" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('tenant.configuration.general.bank_cbu') }}</flux:label>
                            <flux:input wire:model.defer="bank_cbu" placeholder="0000000000000000000000" />
                            <flux:error name="bank_cbu" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('tenant.configuration.general.bank_alias') }}</flux:label>
                            <flux:input wire:model.defer="bank_alias" placeholder="MI.ALIAS.BANCO" />
                            <flux:error name="bank_alias" />
                        </flux:field>
                    </div>
                </div>

                {{-- Mercadopago --}}
                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg space-y-3 mt-3">
                    <flux:checkbox wire:model.defer="accepts_mercadopago" :label="__('tenant.configuration.general.accepts_mercadopago')" />

                    <div x-show="$wire.accepts_mercadopago" class="space-y-3 mt-3">
                        <flux:field>
                            <flux:label>{{ __('tenant.configuration.general.mp_access_token') }}</flux:label>
                            <flux:input wire:model.defer="mp_access_token" type="password" placeholder="APP_USR-..." />
                            <small class="text-xs text-gray-500 mt-1 block">{{ __('tenant.configuration.general.mp_access_token_help') }}</small>
                            <flux:error name="mp_access_token" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('tenant.configuration.general.mp_public_key') }}</flux:label>
                            <flux:input wire:model.defer="mp_public_key" placeholder="APP_USR-..." />
                            <small class="text-xs text-gray-500 mt-1 block">{{ __('tenant.configuration.general.mp_public_key_help') }}</small>
                            <flux:error name="mp_public_key" />
                        </flux:field>
                    </div>
                </div>

                {{-- Efectivo --}}
                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg space-y-3 mt-3">
                    <flux:checkbox wire:model.defer="accepts_cash" :label="__('tenant.configuration.general.accepts_cash')" />

                    <div x-show="$wire.accepts_cash" class="space-y-3 mt-3">
                        <flux:field>
                            <flux:label>{{ __('tenant.configuration.general.cash_instructions') }}</flux:label>
                            <flux:textarea wire:model.defer="cash_instructions" rows="2"
                                :placeholder="__('tenant.configuration.general.cash_instructions_placeholder')" />
                        </flux:field>
                    </div>
                </div>

            </div>

            {{-- Footer compacto --}}
            <div class="pt-6 max-w-md">
                <div class="flex justify-end gap-3 items-center text-sm opacity-80">
                    <x-tenant.action-message class="me-3" on="updated">
                        {{ __('site.saved') }}
                    </x-tenant.action-message>
                    <flux:button type="submit" size="sm">
                        {{ __('site.save') }}
                    </flux:button>
                </div>
            </div>

            <flux:separator variant="subtle" class="mt-2" />
        </form>
    </div>
</div>
