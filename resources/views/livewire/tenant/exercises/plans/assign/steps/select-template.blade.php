<div class="space-y-6">
    <flux:heading size="lg">{{ __('Paso 2 — Seleccionar plantilla') }}</flux:heading>
    <flux:subheading>{{ __('Elegí la plantilla publicada que querés asignar al alumno.') }}</flux:subheading>

    <div class="flex items-center gap-3">
        <flux:input wire:model.live.debounce.500ms="q" placeholder="{{ __('Buscar por nombre o código...') }}" class="w-full" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($templates as $tpl)
            @php
                $weeks = $tpl->workouts->unique('week_index')->count();

                // Total de días únicos dentro de cada semana
                $totalDays = $tpl->workouts
                    ->groupBy('week_index')
                    ->map(fn($week) => $week->unique('day_index')->count())
                    ->sum();

                // Promedio de días por semana (redondeado)
                $avgDays = $weeks ? round($totalDays / $weeks, 1) : 0;

                // Total de ejercicios (sumando blocks/items)
                $exercises = $tpl->workouts
                    ->flatMap(fn($w) => $w->blocks?->flatMap(fn($b) => $b->items ?? collect()) ?? collect())
                    ->count();
            @endphp

            <div
                class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl p-4 md:p-5 dark:bg-neutral-900 dark:border-neutral-700 dark:shadow-neutral-700/70">
                <div class="flex flex-col flex-1">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                        {{ $tpl->name ?: __('(Sin nombre)') }}
                    </h3>
                    <p class="mt-1 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500">
                        {{ __('Código:') }} {{ $tpl->code }}
                    </p>

                    <div class="mt-3 text-sm text-gray-600 dark:text-neutral-400 space-y-1">
                        <p>
                            <span
                                class="font-medium text-gray-700 dark:text-neutral-300">{{ __('Semanas totales:') }}</span>
                            {{ $weeks }}
                        </p>
                        <p>
                            <span
                                class="font-medium text-gray-700 dark:text-neutral-300">{{ __('Promedio días/semana:') }}</span>
                            {{ $avgDays }}
                        </p>
                        <p>
                            <span
                                class="font-medium text-gray-700 dark:text-neutral-300">{{ __('Ejercicios totales:') }}</span>
                            {{ $exercises }}
                        </p>
                        <p class="text-xs italic text-gray-500 dark:text-neutral-500">
                            {{ __('Últ. actualización:') }} {{ $tpl->updated_at?->format('d/m/Y') }}
                        </p>
                    </div>

                    {{-- Separador visual --}}
                    <div class="mt-4 border-t border-gray-200 dark:border-neutral-700"></div>

                    <div class="mt-4 flex justify-end">
                        <flux:button size="sm" variant="primary" wire:click="selectTemplate({{ $tpl->id }})"
                            class="mt-auto inline-flex items-center gap-x-1 self-end">
                            {{ __('Elegir esta plantilla') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center text-gray-500 dark:text-neutral-400 p-6">
                {{ __('No hay plantillas publicadas disponibles.') }}
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $templates->links('components.preline.pagination') }}
    </div>
</div>
