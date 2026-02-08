<div wire:poll.5s>
    <flux:navlist.item icon="message-square-more"
        :badge="$unreadCount > 0 ? ($unreadCount > 99 ? '99+' : (string) $unreadCount) : null"
        badge:color="red"
        href="{{ route('central.dashboard.contacts.index') }}"
        :current="request()->routeIs('central.dashboard.contacts.*')"
        wire:navigate>
        {{ __('site.contacts') }}
    </flux:navlist.item>
</div>
