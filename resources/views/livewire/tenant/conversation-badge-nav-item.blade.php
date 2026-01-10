<div wire:poll.5s>
    <flux:navlist.item icon="chat-bubble-left-right" badge:color="red"
        href="{{ route('tenant.dashboard.messages.conversations.index') }}"
        :current="request()->routeIs('tenant.dashboard.messages.*')"
        :badge="$unreadCount > 0 ? ($unreadCount > 99 ? '99+' : (string)$unreadCount) : null"
        wire:navigate>
        {{ __('Mensajes') }}
    </flux:navlist.item>
</div>
