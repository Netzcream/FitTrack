<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>

            <flux:navlist.item :href="route('tenant.dashboard.configuration.general')"
                :current="request()->routeIs('tenant.dashboard.configuration.general')" wire:navigate>
                {{ __('tenant.configuration.general.title') }}
            </flux:navlist.item>
            <flux:navlist.item :href="route('tenant.dashboard.configuration.notifications')"
                :current="request()->routeIs('tenant.dashboard.configuration.notifications')" wire:navigate>
                {{ __('tenant.configuration.notification.title') }}
            </flux:navlist.item>
            <flux:navlist.item :href="route('tenant.dashboard.configuration.appearance')"
                :current="request()->routeIs('tenant.dashboard.configuration.appearance')" wire:navigate>
                {{ __('tenant.configuration.appearance.title') }}
            </flux:navlist.item>

        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
