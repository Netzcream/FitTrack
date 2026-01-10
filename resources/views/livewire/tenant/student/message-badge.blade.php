<div wire:poll.5s class="inline-block">
    @if ($unreadCount > 0)
        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    @endif
</div>
