@props(['title', 'subtitle', 'icon' => 'dumbbell', 'student' => null])

<div class="flex items-center justify-between flex-wrap gap-4">
    <div>
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-3">
            <x-dynamic-component :component="'icons.lucide.' . $icon" class="w-8 h-8" style="color: var(--ftt-color-base);" />
            {{ $title }}
        </h1>
        <p class="text-zinc-500 dark:text-zinc-400 mt-1">
            {{ $subtitle }}
        </p>
    </div>

    <div class="flex items-center gap-3">
        @php
            $logo = 'https://placehold.co/500x150?text=' . tenant()->name;
        @endphp
        @if (tenant()->config?->getFirstMediaUrl('logo'))
            <img src="{{ tenant()->config->getFirstMediaUrl('logo') }}"
                 alt="{{ tenant()->name ?? 'Gimnasio' }}"
                 class="h-10 max-w-[120px] object-contain opacity-60" />
        @endif
    </div>
</div>
