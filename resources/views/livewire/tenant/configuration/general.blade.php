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
                <div>
                    <flux:input wire:model.defer="name" :label="__('tenant.configuration.general.site_name')"
                        type="text" required autofocus autocomplete="name" />
                </div>


                <div>
                    <flux:input wire:model.defer="whatsapp" :label="__('tenant.configuration.landing.landing_whatsapp')"
                        type="text" autocomplete="tel" />
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
