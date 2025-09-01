@props(['width' => 'max-w-lg'])
<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('tenant.dashboard.landing.general')"
                :current="request()->routeIs('tenant.dashboard.landing.general')" wire:navigate>
                {{ __('tenant.landing.general.title') }}
            </flux:navlist.item>
            <flux:navlist.item :href="route('tenant.dashboard.landing.cards')"
                :current="request()->routeIs('tenant.dashboard.landing.cards')" wire:navigate>
                {{ __('tenant.landing.cards.title') }}
            </flux:navlist.item>
            <flux:navlist.item :href="route('tenant.dashboard.landing.banners')"
                :current="request()->routeIs('tenant.dashboard.landing.banners')" wire:navigate>
                {{ __('tenant.landing.banners.title') }}
            </flux:navlist.item>


            <flux:navlist.item :href="route('tenant.dashboard.landing.booklets')"
                :current="request()->routeIs('tenant.dashboard.landing.booklets')" wire:navigate>
                {{ __('tenant.landing.booklets.title') }}
            </flux:navlist.item>

        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full {{ $width }}">
            {{ $slot }}
        </div>
    </div>
</div>
