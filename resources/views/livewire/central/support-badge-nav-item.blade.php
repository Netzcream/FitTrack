<div wire:poll.5s>
    <flux:navlist.item icon="life-buoy"
        :badge="$unreadCount > 0 ? ($unreadCount > 99 ? '99+' : (string)$unreadCount) : null"
        badge:color="red"
        href="{{ route('central.dashboard.support.index') }}"
        :current="request()->routeIs('central.dashboard.support.*')"
        wire:navigate>
        Soporte
    </flux:navlist.item>
</div>
