@props([
    'student' => null,
    'size' => 'default', // 'compact', 'default', 'large'
    'showProgress' => true,
    'showStats' => true,
])

@php
    $stats = gamification_stats($student);
    $badgeClass = gamification_badge_class($stats['current_tier']);
    $tierIcon = gamification_tier_icon($stats['current_tier']);
@endphp

@if($stats['has_profile'])
    <div {{ $attributes->merge(['class' => 'bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-gray-200 dark:border-neutral-700']) }}>
        {{-- Header --}}
        <div class="p-4 {{ $size === 'compact' ? 'pb-2' : '' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl" title="{{ $stats['tier_name'] }}">{{ $tierIcon }}</span>
                    <div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                            {{ __('gamification.tier_' . $stats['current_tier']) }}
                        </span>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ __('gamification.level') }} {{ $stats['current_level'] }}
                        </p>
                    </div>
                </div>

                @if($size !== 'compact')
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['total_xp']) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">XP</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Progress bar --}}
        @if($showProgress && $size !== 'compact')
            <div class="px-4 pb-3">
                <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-2">
                    <span>{{ __('gamification.level_progress') }}</span>
                    <span>{{ $stats['level_progress'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-neutral-700 rounded-full h-2">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full transition-all duration-500"
                         style="width: {{ $stats['level_progress'] }}%">
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ number_format($stats['xp_for_next_level'] - $stats['total_xp']) }} XP
                    {{ __('gamification.xp_to_next_level') }}
                </p>
            </div>
        @endif

        {{-- Stats footer --}}
        @if($showStats && $size === 'large')
            <div class="px-4 pb-4 pt-2 border-t border-gray-100 dark:border-neutral-700">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('gamification.exercises_completed') }}</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $stats['total_exercises'] }}</p>
                    </div>
                    @if($stats['last_completed'])
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('gamification.last_exercise') }}</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($stats['last_completed'])->diffForHumans() }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@else
    <div {{ $attributes->merge(['class' => 'bg-gray-50 dark:bg-neutral-800 rounded-lg border border-dashed border-gray-300 dark:border-neutral-700 p-6 text-center']) }}>
        <p class="text-gray-600 dark:text-gray-400 mb-2">
            {{ __('gamification.no_activity_yet') }}
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-500">
            {{ __('gamification.start_training') }}
        </p>
    </div>
@endif
