<section class="w-full">
    @include('tenant.partials.configuration-heading')
    @section('title', __('tenant.configuration.appearance.title') . ' - ' . __('site.dashboard'))
    <x-tenant.configuration.layout :heading="__('tenant.configuration.appearance.title')" :subheading="__('tenant.configuration.appearance.subtitle')">

        <form wire:submit="save" class="my-6 w-full space-y-6" enctype="multipart/form-data">


            <x-preline.file-upload name="logo" :label="__('tenant.configuration.appearance.logo')" :preview="$logo" :uploadedUrl="$logoUrl" width="128"
                {{-- o max-w-[128px], etc. --}} height="96" {{-- o max-w-[128px], etc. --}} radius="rounded-md" />


            <x-preline.file-upload name="favicon" :label="__('tenant.configuration.appearance.favicon')" :preview="$favicon" :uploadedUrl="$faviconUrl" width="96"
                {{-- o max-w-[128px], etc. --}} height="96" {{-- o max-w-[128px], etc. --}} radius="rounded-md" />

            {{-- Botón Guardar --}}


            <div class="space-y-1 w-10">
                <label for="color_base"
                    class="block text-sm font-medium text-zinc-700 dark:text-white whitespace-nowrap">
                    {{ __('Color principal') }}
                </label>

                <input type="color" id="color_base" name="color_base" wire:model="color_base" required autofocus
                    autocomplete="color_base"
                    class="cursor-pointer block w-full h-10 p-0 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[{{ tenant_config('color_base', '#263d83') }}]" />

                <label for="color_dark"
                    class="block text-sm font-medium text-zinc-700 dark:text-white whitespace-nowrap">
                    {{ __('Color Oscuro') }}
                </label>
                <input type="color" id="color_dark" name="color_dark" wire:model="color_dark" required autofocus
                    autocomplete="color_dark"
                    class="cursor-pointer block w-full h-10 p-0 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[{{ tenant_config('color_dark', '#3b4f9e') }}]" />
                <label for="color_light"
                    class="block text-sm font-medium text-zinc-700 dark:text-white whitespace-nowrap">
                    {{ __('Color Claro') }}
                </label>
                <input type="color" id="color_light" name="color_light" wire:model="color_light" required autofocus
                    autocomplete="color_light"
                    class="cursor-pointer block w-full h-10 p-0 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[{{ tenant_config('color_light', '#fafafa') }}]" />

            </div>


            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('site.save') }}</flux:button>
                </div>

                <x-tenant.action-message class="me-3" on="updated">
                    {{ __('site.saved') }}
                </x-tenant.action-message>
            </div>

        </form>

    </x-tenant.configuration.layout>
</section>
