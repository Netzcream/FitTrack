@props([
    'type' => 'info', // info, warning, error, success
    'icon' => null,
    'message' => '',
    'action' => null, // ['label' => 'Ver pagos', 'url' => 'route()']
])

@php
    $typeClasses = [
        'info' => 'border-blue-500 bg-blue-50 text-blue-700',
        'warning' => 'border-yellow-500 bg-yellow-50 text-yellow-700',
        'error' => 'border-red-500 bg-red-50 text-red-700',
        'success' => 'border-green-500 bg-green-50 text-green-700',
    ];

    $iconClasses = [
        'info' => 'text-blue-500',
        'warning' => 'text-yellow-500',
        'error' => 'text-red-500',
        'success' => 'text-green-500',
    ];

    $class = $typeClasses[$type] ?? $typeClasses['info'];
    $iconClass = $iconClasses[$type] ?? $iconClasses['info'];
@endphp

<div class="border-l-4 p-4 rounded flex items-start gap-3 {{ $class }}">
    @if ($icon)
        <div class="flex-shrink-0 mt-0.5">
            {{ $icon }}
        </div>
    @endif

    <div class="flex-1 flex items-center justify-between gap-3">
        <div>
            @if ($slot->isNotEmpty())
                {{ $slot }}
            @else
                <p class="text-sm font-medium">{{ $message }}</p>
            @endif
        </div>

        @if ($action)
            <a href="{{ $action['url'] }}" class="text-sm underline hover:opacity-70 transition flex-shrink-0 whitespace-nowrap">
                {{ $action['label'] }}
            </a>
        @endif
    </div>
</div>
