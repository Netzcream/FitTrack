@props([
    'search' => '',
    'searchPlaceholder' => 'Buscar...',
    'additionalFilters' => null,
    'hasActiveFilters' => false,
])

<div class="flex flex-wrap gap-4 w-full items-end">
    {{-- Campo de búsqueda --}}
    <div class="max-w-[260px] flex-1">
        <flux:input
            size="sm"
            class="w-full"
            wire:model.live.debounce.250ms="search"
            label="{{ __('Buscar') }}"
            placeholder="{{ $searchPlaceholder }}" />
    </div>

    {{-- Filtros adicionales (slot opcional) --}}
    @if($additionalFilters)
        {{ $additionalFilters }}
    @endif

    {{-- Botón de limpiar filtros --}}
    <div>
        <flux:button size="sm" variant="ghost" wire:click="clearFilters">
            {{ __('common.clear') }}
        </flux:button>
    </div>
</div>
