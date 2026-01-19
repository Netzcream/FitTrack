@props([
    'student',
    'compact' => false,
])

@php
    $profile = $student->gamificationProfile ?? null;

    if (!$profile) {
        return;
    }

    $currentLevel = $profile->current_level;
    $nextLevel = $currentLevel + 1;
    $progress = $profile->level_progress_percent;
    $tierName = $profile->tier_name;
    $tierIcon = gamification_tier_icon($profile->current_tier);
    $badgeClass = gamification_badge_class($profile->current_tier);
@endphp

<div class="flex items-center gap-3 w-full {{ $compact ? 'text-sm' : '' }}">
    {{-- Nivel actual --}}
    <div class="flex items-center gap-2 shrink-0">
        <span class="font-bold text-gray-900 dark:text-white {{ $compact ? 'text-base' : 'text-lg' }}">
            Nv. {{ $currentLevel }}
        </span>
        @if ($tierIcon)
            <span class="text-xl">{{ $tierIcon }}</span>
        @endif
    </div>

    {{-- Barra de progreso --}}
    <div class="flex-1 min-w-0">
        <div class="relative h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden shadow-inner">
            <div
                class="h-full rounded-full transition-all duration-500 ease-out shadow-sm"
                style="width: {{ $progress }}%; background: linear-gradient(90deg, var(--ftt-color-base) 0%, var(--ftt-color-dark) 100%);">
            </div>
        </div>
        <div class="flex items-center justify-between mt-1 text-xs text-gray-600 dark:text-gray-400">
            <span>{{ number_format($profile->total_xp - $profile->xp_for_current_level) }} XP</span>
            <span class="font-medium">{{ number_format($profile->xp_for_next_level - $profile->xp_for_current_level) }} XP</span>
        </div>
    </div>

    {{-- Próximo nivel --}}
    <div class="flex items-center gap-2 shrink-0">
        <span class="font-bold text-gray-700 dark:text-gray-300 {{ $compact ? 'text-base' : 'text-lg' }}">
            Nv. {{ $nextLevel }}
        </span>
    </div>

    {{-- Badge del tier --}}
    <div class="shrink-0">
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
            @if ($tierIcon)
                <span>{{ $tierIcon }}</span>
            @endif
            <span>{{ $tierName }}</span>
        </span>
    </div>
</div>
