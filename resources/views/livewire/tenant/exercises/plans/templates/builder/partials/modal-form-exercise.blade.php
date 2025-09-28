<flux:modal name="exercise-editor" class="max-w-3xl" wire:key="exercise-editor"
    @modal-closed.window="if ($event.detail?.name === 'exercise-editor') { $wire.call('resetEditor') }">
    {{-- Alpine: el skeleton depende de editorReady (no de wire:loading) --}}
    <div x-data="{ ready: @entangle('editorReady') }" class="space-y-6">

        {{-- SKELETON (mismo layout que el contenido real) --}}
        <div x-show="!ready" x-cloak class="space-y-6" aria-hidden="true">
            {{-- Header --}}
            <div>
                <div class="h-7 w-64 rounded bg-zinc-700/70 dark:bg-zinc-700 animate-pulse"></div>
                <div class="mt-2 h-4 w-80 rounded bg-zinc-700/60 dark:bg-zinc-700/60 animate-pulse"></div>
            </div>

            {{-- Grid: buscador / bloque / resumen (md:grid-cols-4) --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                {{-- Col izquierda (md:col-span-2) --}}
                <div class="md:col-span-2 space-y-2">
                    <div class="h-4 w-32 rounded bg-zinc-700/60 animate-pulse"></div>
                    <div class="h-10 w-full rounded-md bg-zinc-700/70 animate-pulse"></div>
                </div>

                {{-- Select de bloque --}}
                <div class="space-y-2">
                    <div class="h-4 w-24 rounded bg-zinc-700/60 animate-pulse"></div>
                    <div class="h-10 w-full rounded-md bg-zinc-700/70 animate-pulse"></div>
                </div>

                {{-- Resumen selección --}}
                <div class="flex items-end">
                    <div class="h-4 w-48 rounded bg-zinc-700/60 animate-pulse"></div>
                </div>
            </div>

            {{-- Prescripción (sólo placeholders) --}}
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="space-y-2">
                    <div class="h-4 w-24 rounded bg-zinc-700/60 animate-pulse"></div>
                    <div class="h-10 w-full rounded-md bg-zinc-700/70 animate-pulse"></div>
                </div>
                <div class="space-y-2">
                    <div class="h-4 w-20 rounded bg-zinc-700/60 animate-pulse"></div>
                    <div class="h-10 w-full rounded-md bg-zinc-700/70 animate-pulse"></div>
                </div>
                @for ($i = 0; $i < 4; $i++)
                    <div class="space-y-2">
                        <div class="h-4 w-24 rounded bg-zinc-700/60 animate-pulse"></div>
                        <div class="h-10 w-full rounded-md bg-zinc-700/70 animate-pulse"></div>
                    </div>
                @endfor
            </div>

            {{-- Acciones --}}
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">{{ __('Cerrar') }}</flux:button>
                </flux:modal.close>
                <flux:button type="button" disabled>
                    <span class="opacity-60">{{ __('Procesando…') }}</span>
                </flux:button>
            </div>
        </div>

        {{-- CONTENIDO REAL (aparece cuando ready=true) --}}
        <div x-show="ready" x-cloak class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editorMode === 'add' ? __('Agregar ejercicio — ') : __('Editar ejercicio — ') }}
                    @if ($editorMode === 'add')
                        {{ $template->workouts->firstWhere('id', $editorWorkoutId)?->name ?? '' }}
                    @else
                        {{ $editorForm['display_name'] ?? '' }}
                    @endif
                </flux:heading>
                <flux:text class="mt-1 text-gray-600 dark:text-gray-300">
                    {{ __('Elegí el ejercicio, bloque y prescripción') }}
                </flux:text>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                {{-- BUSCAR / LISTA --}}
                <div class="md:col-span-2">
                    <flux:input wire:model="editorQuery" wire:keyup.debounce.300ms="searchExercisesUnified"
                        label="{{ __('Buscar ejercicio') }}" placeholder="{{ __('Nombre o código') }}" />

                    {{-- Placeholder de *búsqueda* --}}
                    <div class="mt-2 rounded-lg border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow"
                        wire:loading wire:target="editorQuery,searchExercisesUnified">
                        @for ($i = 0; $i < 5; $i++)
                            <div class="px-3 py-2">
                                <div class="h-4 w-48 rounded bg-gray-200 dark:bg-zinc-800 animate-pulse"></div>
                            </div>
                        @endfor
                    </div>

                    <div class="mt-2 rounded-lg border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow max-h-52 overflow-auto"
                        wire:loading.remove wire:target="editorQuery,searchExercisesUnified">
                        @if (!empty($editorResults))
                            @foreach ($editorResults as $res)
                                <div class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                    wire:click="chooseExerciseUnified({{ $res['id'] }})">
                                    <span class="text-gray-900 dark:text-gray-100">{{ $res['name'] }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">·
                                        {{ $res['default_modality'] }}</span>
                                </div>
                            @endforeach
                        @elseif ($editorQuery !== '')
                            <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Sin resultados') }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- BLOQUE --}}
                <div>
                    <flux:label class="text-xs">{{ $editorMode === 'add' ? __('Agregar en') : __('Mover a') }}
                    </flux:label>
                    <flux:select wire:model.live="editorBlockType">
                        <option value="warmup">{{ __('Calentamiento') }}</option>
                        <option value="main">{{ __('Principal') }}</option>
                        <option value="cooldown">{{ __('Enfriamiento') }}</option>
                    </flux:select>
                </div>

                {{-- RESUMEN --}}
                <div class="flex items-end">
                    <div class="text-xs text-gray-600 dark:text-gray-300">
                        @if ($editorForm['exercise_id'])
                            {{ __('Seleccionado:') }} {{ $editorForm['display_name'] }}
                            ({{ $editorForm['modality'] }})
                        @else
                            <span class="inline-flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-gray-300 dark:bg-zinc-700"></span>
                                {{ __('Sin selección') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>


            @php
                $mod = $editorForm['modality'] ?? 'reps';
                $cfg = [
                    'reps' => [
                        'key' => 'reps',
                        'label' => __('Repeticiones por serie'),
                        'type' => 'number',
                        'attrs' => ['min' => 0],
                    ],
                    'time' => [
                        'key' => 'time_seconds_arr',
                        'label' => __('Tiempo (seg) por serie'),
                        'type' => 'number',
                        'attrs' => ['min' => 1],
                    ],
                    'distance' => [
                        'key' => 'distance_meters_arr',
                        'label' => __('Distancia (m) por serie'),
                        'type' => 'number',
                        'attrs' => ['min' => 1],
                    ],
                    'calories' => [
                        'key' => 'calories_arr',
                        'label' => __('Calorías por serie'),
                        'type' => 'number',
                        'attrs' => ['min' => 1],
                    ],
                    'rpe' => [
                        'key' => 'target_rpe_arr',
                        'label' => __('RPE objetivo por serie'),
                        'type' => 'number',
                        'attrs' => ['min' => 1, 'max' => 10],
                    ],
                    'load_only' => [
                        'key' => 'load_arr',
                        'label' => __('Carga (kg) por serie'),
                        'type' => 'number',
                        'attrs' => ['step' => 0.5],
                    ],
                    'tempo_only' => [
                        'key' => 'tempo_arr',
                        'label' => __('Tempo por serie'),
                        'type' => 'text',
                        'attrs' => [],
                    ],
                ];
                $c = $cfg[$mod] ?? $cfg['reps'];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                {{-- Modalidad --}}
                <div>
                    <flux:label class="text-xs">{{ __('Modalidad') }}</flux:label>
                    <flux:select wire:model.live="editorForm.modality">
                        <option value="reps">reps</option>
                        <option value="time">time</option>
                        <option value="distance">distance</option>
                        <option value="calories">calories</option>
                        <option value="rpe">rpe</option>
                        <option value="load_only">load_only</option>
                        <option value="tempo_only">tempo_only</option>
                    </flux:select>
                </div>

                <div>
                    <flux:label class="text-xs">{{ __('Series') }}</flux:label>
                    <flux:select wire:model.live="editorForm.sets">
                        @for ($s = 1; $s <= 6; $s++)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endfor
                    </flux:select>
                </div>
                {{-- INPUTS DINÁMICOS POR SERIE (réplica de flux con HTML nativo, más compacto) --}}
                <div class="hidden md:block md:col-span-4"></div>

                <div class="md:col-span-6">
                    <label class="text-xs block mb-1">{{ $c['label'] }}</label>

                    <div class="mt-0 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                        @for ($i = 0; $i < intval($editorForm['sets'] ?? 0); $i++)
                            @php
                                $wireModel = "editorForm.{$c['key']}.$i";
                                $isNumber = $c['type'] === 'number';
                                $attrs = $c['attrs'] ?? [];
                                $labelId = "fld-{$c['key']}-{$i}-label";
                            @endphp

                            <ui-field
                                class="min-w-0
                       [&:not(:has([data-flux-field])):has([data-flux-control][disabled])>[data-flux-label]]:opacity-50
                       [&:has(>[data-flux-radio-group][disabled])>[data-flux-label]]:opacity-50
                       [&:has(>[data-flux-checkbox-group][disabled])>[data-flux-label]]:opacity-50
                       block
                       [&>[data-flux-label]:has(+[data-flux-description])]:mb-1
                       [&>*:not([data-flux-label])+[data-flux-description]]:mt-2"
                                data-flux-field wire:key="setval-{{ $c['key'] }}-{{ $i }}">
                                {{-- Label compacto de la serie --}}
                                <ui-label
                                    class="inline-flex items-center text-[11px] leading-4 font-medium text-zinc-500 dark:text-zinc-400 mb-1"
                                    data-flux-label id="{{ $labelId }}" aria-hidden="true">
                                    {{ __('S') }}{{ $i + 1 }}
                                </ui-label>

                                <div class="w-full relative block group/input" data-flux-input>
                                    <input type="{{ $isNumber ? 'number' : 'text' }}"
                                        @if ($isNumber && isset($attrs['min'])) min="{{ $attrs['min'] }}" @endif
                                        @if ($isNumber && isset($attrs['max'])) max="{{ $attrs['max'] }}" @endif
                                        @if ($isNumber && isset($attrs['step'])) step="{{ $attrs['step'] }}" @endif
                                        wire:model.live="{{ $wireModel }}" name="{{ $wireModel }}"
                                        aria-labelledby="{{ $labelId }}" data-flux-control data-flux-group-target
                                        inputmode="{{ $isNumber ? 'numeric' : 'text' }}"
                                        placeholder="{{ $isNumber ? '0' : '' }}"
                                        class="w-full border rounded-lg block disabled:shadow-none dark:shadow-none appearance-none
                               text-base sm:text-sm py-2 h-10 leading-[1.375rem] ps-3 pe-3
                               bg-white dark:bg-white/10 dark:disabled:bg-white/[7%]
                               text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70
                               dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500
                               shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200
                               dark:border-white/10 dark:disabled:border-white/5" />
                                </div>
                            </ui-field>
                        @endfor
                    </div>
                </div>



                {{-- Campos comunes --}}
                <flux:input type="number" min="0" wire:model.defer="editorForm.rest_seconds"
                    label="{{ __('Descanso (seg)') }}" />
                <flux:input wire:model.defer="editorForm.notes" label="{{ __('Notas') }}" />
            </div>

            {{-- Acciones --}}
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button type="button" variant="ghost" x-on:click="$wire.call('resetEditor')">
                        {{ __('Cancelar') }}
                    </flux:button>
                </flux:modal.close>
                <flux:button type="button" wire:click="saveEditor">
                    {{ $editorMode === 'add' ? __('Agregar') : __('Guardar cambios') }}
                </flux:button>
            </div>
        </div>
    </div>
</flux:modal>
