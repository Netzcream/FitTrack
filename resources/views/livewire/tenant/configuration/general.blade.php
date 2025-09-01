<section class="w-full">
    @include('tenant.partials.configuration-heading')
    @section('title', __('tenant.configuration.general.title') . ' - ' . __('site.dashboard'))

    <x-tenant.configuration.layout :heading="__('tenant.configuration.general.title')" :subheading="__('tenant.configuration.general.subtitle')">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <div>
                <flux:input wire:model="name" :label="__('tenant.configuration.general.site_name')" type="text"
                    required autofocus autocomplete="name" />
            </div>
            <div>
                <flux:input wire:model="whatsapp" :label="__('tenant.configuration.landing.landing_whatsapp')"
                    type="text" autofocus autocomplete="name" />
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
