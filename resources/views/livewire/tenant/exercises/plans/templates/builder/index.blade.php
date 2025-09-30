<div class="space-y-8" x-data x-on:template-header-updated.window="$wire.call('refreshTemplate')">
    <div class="flex items-start justify-between">
        <div>
            <flux:heading size="xl">{{ __('Builder de plantilla') }}
            </flux:heading>
            <flux:subheading>
                {{ __('Versión') }} {{ $template->version }}
            </flux:subheading>
        </div>
    </div>

    {{-- Estructura rápida (colores neutrales) --}}
    <div class="rounded-2xl border border-gray-200 dark:border-zinc-800 p-4 space-y-4 bg-white/60 dark:bg-zinc-900/60">
        <flux:heading size="md">{{ __('Estructura rápida') }}</flux:heading>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <flux:input type="number" min="1" max="52" wire:model.defer="weeks_count"
                label="{{ __('Semanas') }}" />
            <flux:input type="number" min="1" max="7" wire:model.defer="days_per_week"
                label="{{ __('Días por semana') }}" />
            <div class="md:col-span-3 flex items-end">
                <flux:button wire:click="scaffoldWeeks">{{ __('Generar') }}</flux:button>
            </div>
        </div>
    </div>

    {{-- Semanas y días --}}
    <div class="rounded-2xl border border-gray-200 dark:border-zinc-800 p-4 space-y-3 bg-white/60 dark:bg-zinc-900/60">
        <flux:heading size="md">{{ __('Semanas y días') }}</flux:heading>

        <div class="flex gap-2 flex-wrap">
            @php $weeks = $template->workouts->groupBy('week_index'); @endphp
            @foreach ($weeks as $wIndex => $days)
                <flux:button size="sm" variant="{{ (int) $selected_week === (int) $wIndex ? 'primary' : 'ghost' }}"
                    wire:click="$set('selected_week', {{ $wIndex }})">
                    {{ __('Semana') }} {{ $wIndex }}
                </flux:button>
            @endforeach
        </div>

        @if ($selected_week && isset($weeks[$selected_week]))
            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium">{{ __('Días de la semana') }} {{ $selected_week }}</div>
                    <flux:button size="sm" wire:click="addDay({{ $selected_week }})">{{ __('Agregar día') }}
                    </flux:button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach ($weeks[$selected_week] as $w)
                        <div wire:key="workout-{{ $w->id }}"
                            class="rounded-xl border border-gray-200 dark:border-zinc-800 p-3 space-y-3 bg-white/40 dark:bg-zinc-900/40">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold">{{ $w->name ?? 'Día ' . $w->day_index }}</div>
                                <flux:button size="sm" variant="ghost"
                                    wire:click="removeDay({{ $w->id }})">{{ __('Eliminar día') }}</flux:button>
                            </div>

                            {{-- Acción simple + TRIGGER del modal --}}
                            <div
                                class="rounded-lg border border-gray-200 dark:border-zinc-800 p-3 bg-white/60 dark:bg-zinc-900/60">
                                <div class="flex items-center justify-between">
                                    <div class="text-xs text-gray-600 dark:text-gray-300">
                                        {{ __('Usá el botón para agregar ejercicios') }}
                                    </div>
                                    <flux:modal.trigger name="exercise-editor">
                                        <flux:button type="button" wire:click="openAddModal({{ $w->id }})">
                                            {{ __('Agregar ejercicio') }}
                                        </flux:button>
                                    </flux:modal.trigger>

                                </div>
                            </div>

                            {{-- Bloques e ítems --}}
                            @foreach ($w->blocks as $b)
                                <div wire:key="block-{{ $b->id }}"
                                    class="rounded-lg border border-gray-200 dark:border-zinc-800 p-2">
                                    <div class="text-sm font-semibold">
                                        {{ ucfirst($b->type->label()) }} {{ $b->name ? '— ' . $b->name : '' }}
                                    </div>
                                    <ul class="mt-2 space-y-1">
                                        @foreach ($b->items as $it)
                                            @php
                                                $pr = $it->prescription ?? [];
                                                $repStr =
                                                    isset($pr['reps']) && is_array($pr['reps']) && count($pr['reps'])
                                                        ? implode(' / ', $pr['reps'])
                                                        : null;
                                            @endphp
                                            <li wire:key="item-{{ $it->id }}"
                                                class="flex items-center justify-between rounded border border-gray-200 dark:border-zinc-800 p-2 bg-white dark:bg-zinc-900">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $it->display_name ?? ($it->exercise?->name ?? __('(sin nombre)')) }}
                                                    @if ($pr && ($pr['sets'] ?? null))
                                                        — {{ $pr['sets'] }}x
                                                        @switch($pr['modality'] ?? '')
                                                            @case('reps')
                                                                {{ $repStr ?? '?' }}
                                                            @break

                                                            @case('time')
                                                                {{ $pr['time_seconds'] ?? '?' }}s
                                                            @break

                                                            @case('distance')
                                                                {{ $pr['distance_meters'] ?? '?' }}m
                                                            @break

                                                            @case('calories')
                                                                {{ $pr['calories'] ?? '?' }} cal
                                                            @break

                                                            @case('rpe')
                                                                RPE {{ $pr['target_rpe'] ?? '?' }}
                                                            @break

                                                            @case('load_only')
                                                                {{ $pr['load'] ?? '?' }}kg
                                                            @break

                                                            @case('tempo_only')
                                                                {{ $pr['tempo'] ?? '?' }}
                                                            @break
                                                        @endswitch
                                                    @endif
                                                </div>
                                                <div class="flex gap-2">
                                                    <flux:modal.trigger name="exercise-editor">
                                                        <flux:button size="sm" variant="ghost"
                                                            wire:click="openEditModal({{ $it->id }})">
                                                            {{ __('Editar') }}
                                                        </flux:button>
                                                    </flux:modal.trigger>

                                                    <flux:button size="sm" variant="ghost"
                                                        wire:click="removeItem({{ $it->id }})">
                                                        {{ __('Quitar') }}
                                                    </flux:button>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach

                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @include('livewire.tenant.exercises.plans.templates.builder.partials.modal-form-exercise')
</div>
