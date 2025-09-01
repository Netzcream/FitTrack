<section class="w-full">
    @include('tenant.partials.configuration-heading')
    @section('title', __('tenant.configuration.notification.title') . ' - ' . __('site.dashboard'))
    <x-tenant.configuration.layout :heading="__('tenant.configuration.notification.title')" :subheading="__('tenant.configuration.notification.subtitle')">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <flux:input wire:model="contact_email" :label="__('tenant.configuration.notification.contact_email')"
                type="text" required autofocus autocomplete="contact_email" />

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
