<div wire:poll.5s class="inline-block">
    @if ($unreadCount > 0)
        <flux:badge size="sm" color="red" class="ml-2">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </flux:badge>
    @endif
</div>
