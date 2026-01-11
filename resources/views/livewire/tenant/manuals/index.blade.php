<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">

        {{-- Header --}}
        <div class="mb-6">
            <flux:heading size="xl" level="1">
                {{ __('Manuales y Guías') }}
            </flux:heading>
            <flux:subheading>
                {{ __('Documentación y recursos de ayuda') }}
            </flux:subheading>
        </div>

        {{-- Filtros y búsqueda --}}
        <div class="mb-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Búsqueda --}}
                <div class="md:col-span-2">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        type="search"
                        placeholder="{{ __('Buscar manuales...') }}"
                        icon="magnifying-glass"
                    />
                </div>

                {{-- Filtro por categoría --}}
                <div>
                    <flux:select wire:model.live="categoryFilter">
                        <option value="">{{ __('Todas las categorías') }}</option>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
        </div>

        {{-- Grid de manuales --}}
        @if ($manuals->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                @foreach ($manuals as $manual)
                    <div wire:key="manual-{{ $manual->uuid }}" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-5 hover:shadow-lg transition-shadow cursor-pointer">
                        <a href="{{ route('tenant.dashboard.manuals.show', $manual) }}" wire:navigate class="block">
                            {{-- Icono del manual --}}
                            @if ($manual->hasMedia('icon'))
                                <div class="mb-4">
                                    <img
                                        src="{{ $manual->getFirstMediaUrl('icon') }}"
                                        alt="{{ $manual->title }}"
                                        class="w-full h-48 object-cover rounded-lg"
                                    >
                                </div>
                            @else
                                <div class="mb-4 flex items-center justify-center h-48 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                                    <flux:icon.document-text variant="outline" class="w-16 h-16 text-zinc-400" />
                                </div>
                            @endif

                            {{-- Categoría --}}
                            <div class="mb-2">
                                <flux:badge size="sm" color="zinc">
                                    <x-dynamic-component :component="'icons.lucide.' . $manual->category->icon()" class="w-3.5 h-3.5 me-1" />
                                    {{ $manual->category->label() }}
                                </flux:badge>
                            </div>

                            {{-- Título --}}
                            <flux:heading size="lg" class="mb-2">
                                {{ $manual->title }}
                            </flux:heading>

                            {{-- Resumen --}}
                            @if ($manual->summary)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3 line-clamp-3">
                                    {{ $manual->summary }}
                                </p>
                            @endif

                            {{-- Fecha de publicación --}}
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon.calendar variant="micro" />
                                <span>{{ $manual->published_at->format('d/m/Y') }}</span>
                            </div>

                            {{-- Indicador de archivos adjuntos --}}
                            @if ($manual->hasMedia('attachments'))
                                <div class="mt-3 flex items-center gap-2 text-xs text-zinc-500">
                                    <flux:icon.paper-clip variant="micro" />
                                    <span>{{ $manual->getMedia('attachments')->count() }} {{ __('archivos adjuntos') }}</span>
                                </div>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- Paginación --}}
            <div class="mt-6">
                {{ $manuals->links() }}
            </div>
        @else
            {{-- Estado vacío --}}
            <div class="text-center py-12">
                <flux:icon.document-text variant="outline" class="w-16 h-16 mx-auto text-zinc-400 mb-4" />
                <flux:heading size="lg" class="mb-2">
                    {{ __('No se encontraron manuales') }}
                </flux:heading>
                <p class="text-zinc-600 dark:text-zinc-400">
                    @if ($search || $categoryFilter)
                        {{ __('Intenta ajustar los filtros de búsqueda') }}
                    @else
                        {{ __('No hay manuales disponibles en este momento') }}
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
