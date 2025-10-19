{{-- resources/views/livewire/tenant/configuration/notification.blade.php --}}
<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6">

            {{-- Header sticky --}}
            <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
                <div class="flex items-center justify-between gap-4 max-w-md">
                    <div>
                        <flux:heading size="xl" level="1">
                            {{ __('tenant.configuration.notification.title') }}
                        </flux:heading>
                        <flux:subheading size="lg" class="mb-6">
                            {{ __('tenant.configuration.notification.subtitle') }}
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
            <div class="max-w-md flex items-end gap-2 max-sm:flex-col">

                <div class="grow">
                    <flux:input wire:model.defer="contact_email"
                        :label="__('tenant.configuration.notification.contact_email')" type="email" required autofocus
                        autocomplete="email" />
                </div>
                <div class="shrink-0">
                    <flux:button variant="ghost" type="button" wire:click="testContactEmail">
                        {{ __('site.test') ?: 'Probar' }}
                    </flux:button>
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
