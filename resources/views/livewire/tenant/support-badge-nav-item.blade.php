<div wire:poll.5s>
    <flux:navlist.item icon="life-buoy"
        href="{{ route('tenant.dashboard.support.show') }}"
        :current="request()->routeIs('tenant.dashboard.support.*')"
        badge:color="red"
        :badge="$unreadCount > 0 ? ($unreadCount > 99 ? '99+' : (string)$unreadCount) : null"
        wire:navigate>
        Soporte
    </flux:navlist.item>
</div>
