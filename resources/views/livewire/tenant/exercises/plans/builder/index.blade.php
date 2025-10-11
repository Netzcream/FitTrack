<div class="space-y-8">
    <div class="flex items-start justify-between">
        <div>
            <flux:heading size="xl">{{ __('Builder de plan') }}</flux:heading>
            <flux:subheading>{{ __('Personalizá el plan instanciado') }}</flux:subheading>
        </div>
    </div>

    @php $weeks = $plan->workouts->groupBy('week_index'); @endphp

    <div class="rounded-2xl border border-gray-200 dark:border-zinc-800 p-4 space-y-3 bg-white/60 dark:bg-zinc-900/60">
        <flux:heading size="md">{{ __('Semanas y días') }}</flux:heading>

        <div class="flex gap-2 flex-wrap">
            @foreach ($weeks as $wIndex => $days)
                <flux:button size="sm" variant="{{ (int)$selected_week === (int)$wIndex ? 'primary' : 'ghost' }}"
                    wire:click="$set('selected_week', {{ $wIndex }})">
                    {{ __('Semana') }} {{ $wIndex }}
                </flux:button>
            @endforeach
        </div>

        @if ($selected_week && isset($weeks[$selected_week]))
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach ($weeks[$selected_week] as $w)
                    <div wire:key="workout-{{ $w->id }}"
                        class="rounded-xl border border-gray-200 dark:border-zinc-800 p-3 space-y-3 bg-white/40 dark:bg-zinc-900/40">
                        <div class="flex items-center justify-between">
                            <div class="font-semibold">{{ $w->name ?? 'Día ' . $w->day_index }}</div>
                        </div>

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
                                            $repStr = isset($pr['reps']) && is_array($pr['reps']) ? implode(' / ', $pr['reps']) : null;
                                        @endphp
                                        <li wire:key="item-{{ $it->id }}"
                                            class="flex items-center justify-between rounded border border-gray-200 dark:border-zinc-800 p-2 bg-white dark:bg-zinc-900">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ $it->display_name ?? ($it->exercise?->name ?? __('(sin nombre)')) }}
                                                @if ($pr && ($pr['sets'] ?? null))
                                                    — {{ $pr['sets'] }}x {{ $repStr ?? '?' }}
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
        @endif
    </div>

    @include('livewire.tenant.exercises.plans.builder.partials.modal-form-exercise')
</div>
