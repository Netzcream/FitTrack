<section class="w-full">
    @include('tenant.partials.landing-heading')

    @section('title', __('tenant.landing.general.title') . ' - ' . __('site.dashboard'))

    <x-tenant.landing.layout :heading="__('tenant.landing.general.title')" :subheading="__('tenant.landing.general.subtitle')">

        <form wire:submit="save" class="my-6 w-full space-y-6" enctype="multipart/form-data">


            <flux:input wire:model="title" :label="__('tenant.landing.general.landing_title')" type="text" autofocus
                autocomplete="name" />
            <flux:input wire:model="subtitle" :label="__('tenant.landing.general.landing_subtitle')" type="text"
                autofocus autocomplete="name" />


            <flux:textarea wire:model="description" :label="__('tenant.landing.general.landing_description')"
                type="text" autofocus autocomplete="name" />

            <flux:textarea wire:model="footer" :label="__('tenant.landing.general.landing_footer')" type="text"
                autofocus autocomplete="name" />


            <div class="space-y-1 w-10">
                <label for="color_base"
                    class="block text-sm font-medium text-zinc-700 dark:text-white whitespace-nowrap">
                    {{ __('tenant.landing.general.landing_footer_text_color') }}
                </label>

                <input type="color" id="footerText" name="footerText" wire:model="footerText" required autofocus
                    autocomplete="footerText"
                    class="cursor-pointer block w-full h-10 p-0 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[{{ tenant_config('footerText', '#6a7282') }}]" />

                <label for="color_base"
                    class="block text-sm font-medium text-zinc-700 dark:text-white whitespace-nowrap">
                    {{ __('tenant.landing.general.landing_footer_background_color') }}
                </label>
                <input type="color" id="footerBackground" name="footerBackground" wire:model="footerBackground"
                    required autofocus autocomplete="footerBackground"
                    class="cursor-pointer block w-full h-10 p-0 border border-zinc-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[{{ tenant_config('footerBackground', '#3333333') }}]" />

            </div>

            <x-preline.file-upload name="cover" :label="__('tenant.site.cover')" :preview="$cover" :uploadedUrl="$coverUrl" width="128"
                height="96" radius="rounded-md" />

            <div class="flex items-center space-x-2">
                <input id="show_form" type="checkbox" wire:model="show_form" @checked($show_form)
                    class="rounded border-gray-300 text-sky-600 shadow-sm focus:ring-sky-500" />
                <label for="show_form" class="text-sm text-zinc-800 dark:text-white">¿Mostrar formulario de contacto al
                    pié?</label>
            </div>


            <flux:separator variant="subtle" />




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
