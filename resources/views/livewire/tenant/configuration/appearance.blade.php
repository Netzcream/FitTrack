{{-- resources/views/livewire/tenant/configuration/appearance.blade.php --}}
<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6" enctype="multipart/form-data">

            {{-- Header sticky --}}
            <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
                <div class="flex items-center justify-between gap-4 max-w-xl">
                    <div>
                        <flux:heading size="xl" level="1">
                            {{ __('tenant.configuration.appearance.title') }}
                        </flux:heading>
                        <flux:subheading size="lg" class="mb-6">
                            {{ __('tenant.configuration.appearance.subtitle') }}
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
            <div class="max-w-xl space-y-6 pt-2">

                {{-- Subida de imágenes --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Logo --}}
                    <div>
                        <x-preline.file-upload name="logo" :label="__('tenant.configuration.appearance.logo')" :preview="$logo" :uploadedUrl="$logoUrl"
                            width="160" height="96" radius="rounded-md" wire:key="logo-upload" />
                    </div>
                    <div>
                        {{-- Favicon --}}
                        <x-preline.file-upload name="favicon" :label="__('tenant.configuration.appearance.favicon')" :preview="$favicon" :uploadedUrl="$faviconUrl"
                            width="96" height="96" radius="rounded-md" wire:key="favicon-upload" />
                    </div>
                </div>

                {{-- Selector de colores --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <flux:label class="text-xs mb-2">{{ __('Color principal') }}</flux:label>
                        <input type="color" id="color_base" wire:model.defer="color_base"
                            class="w-full h-10 cursor-pointer rounded-md border border-gray-300 dark:border-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[{{ tenant_config('color_base', '#263d83') }}]" />
                    </div>

                    <div>
                        <flux:label class="text-xs mb-2">{{ __('Color oscuro') }}</flux:label>
                        <input type="color" id="color_dark" wire:model.defer="color_dark"
                            class="w-full h-10 cursor-pointer rounded-md border border-gray-300 dark:border-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[{{ tenant_config('color_dark', '#3b4f9e') }}]" />
                    </div>

                    <div>
                        <flux:label class="text-xs mb-2">{{ __('Color claro') }}</flux:label>
                        <input type="color" id="color_light" wire:model.defer="color_light"
                            class="w-full h-10 cursor-pointer rounded-md border border-gray-300 dark:border-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[{{ tenant_config('color_light', '#fafafa') }}]" />
                    </div>
                </div>
            </div>

            {{-- Footer compacto --}}
            <div class="pt-6 max-w-xl">
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
