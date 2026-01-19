@props([
    'student' => null,
    'showLevel' => true,
    'showIcon' => true,
    'size' => 'md', // 'sm', 'md', 'lg'
])

@php
    $stats = gamification_stats($student);
    $badgeClass = gamification_badge_class($stats['current_tier']);
    $tierIcon = gamification_tier_icon($stats['current_tier']);

    $sizeClasses = match($size) {
        'sm' => 'text-xs px-2 py-0.5',
        'md' => 'text-sm px-3 py-1',
        'lg' => 'text-base px-4 py-2',
        default => 'text-sm px-3 py-1',
    };
@endphp

@if($stats['has_profile'])
    <div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
        @if($showIcon)
            <span class="{{ $size === 'sm' ? 'text-base' : ($size === 'lg' ? 'text-2xl' : 'text-xl') }}">
                {{ $tierIcon }}
            </span>
        @endif

        <span class="rounded-full font-semibold {{ $badgeClass }} {{ $sizeClasses }}">
            {{ __('gamification.tier_' . $stats['current_tier']) }}
            @if($showLevel)
                <span class="opacity-75">· {{ $stats['current_level'] }}</span>
            @endif
        </span>
    </div>
@endif
