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
            <div class="max-w-md space-y-4 pt-2">

                {{-- Nombre del sitio --}}
                <flux:field>
                    <flux:label>{{ __('tenant.configuration.general.site_name') }}</flux:label>
                    <flux:input wire:model.defer="name" type="text" required autofocus autocomplete="name" />
                    <flux:error name="name" />
                </flux:field>

                {{-- WhatsApp --}}
                <flux:field>
                    <flux:label>{{ __('tenant.configuration.landing.landing_whatsapp') }}</flux:label>
                    <flux:input.group>
                        <flux:input.group.prefix>wa.me/</flux:input.group.prefix>
                        <flux:input wire:model.defer="whatsapp" placeholder="5491123456789" autocomplete="tel" />
                    </flux:input.group>
                    <small class="text-xs text-gray-500 mt-1 block">Ingresá solo el número, sin + ni espacios.</small>
                    <flux:error name="whatsapp" />
                </flux:field>

                {{-- Sección de redes --}}
                <flux:heading size="md" class="mt-8">{{ __('tenant.configuration.general.social_title') ?? 'Redes' }}</flux:heading>
                <flux:separator variant="subtle" class="my-2" />

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
