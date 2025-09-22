<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6">
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('exercise.exercises') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">
                        {{ __('exercise.exercises_subheading') }}
                    </flux:subheading>
                </div>
                <flux:button as="a" href="{{ route('tenant.dashboard.exercise.exercises.create') }}"
                    variant="primary" icon="plus">
                    {{ __('exercise.new_exercise') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        <section class="w-full">
            @php
                /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $exercises */
            @endphp
            <x-data-table :pagination="$exercises">
                <x-slot name="filters">
                    <div class="flex flex-wrap gap-4 w-full items-end">
                        {{-- Buscar --}}
                        <div class="max-w-[260px] flex-1">
                            <flux:label class="text-xs">{{ __('exercise.search') }}</flux:label>
                            <flux:input size="sm" wire:model.live.debounce.400ms="search"
                                placeholder="{{ __('exercise.search_placeholder') }}" class="w-full" />
                        </div>

                        {{-- Estado --}}
                        <div class="max-w-[180px]">
                            <flux:label class="text-xs">{{ __('exercise.status') }}</flux:label>
                            <flux:select wire:model.live="status" size="sm" class="w-full">
                                <option value="">{{ __('site.select') }}</option>

                                <option value="{{ \App\Models\Tenant\Exercise\Exercise::STATUS_DRAFT }}">
                                    {{ __('exercise.status_draft') }}</option>
                                <option value="{{ \App\Models\Tenant\Exercise\Exercise::STATUS_PUBLISHED }}">
                                    {{ __('exercise.status_published') }}</option>
                                <option value="{{ \App\Models\Tenant\Exercise\Exercise::STATUS_ARCHIVED }}">
                                    {{ __('exercise.status_archived') }}</option>
                            </flux:select>
                        </div>

                        {{-- Nivel --}}
                        <div class="max-w-[220px]">
                            <flux:label class="text-xs">{{ __('exercise.level') }}</flux:label>
                            <flux:select wire:model.live="exercise_level_id" size="sm" class="w-full">
                                <option value="">{{ __('site.select') }}</option>

                                @foreach ($levels as $l)
                                    <option value="{{ $l->id }}">{{ $l->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        {{-- Patrón --}}
                        <div class="max-w-[220px]">
                            <flux:label class="text-xs">{{ __('exercise.movement_pattern') }}</flux:label>
                            <flux:select wire:model.live="movement_pattern_id" size="sm" class="w-full">
                                <option value="">{{ __('site.select') }}</option>

                                @foreach ($patterns as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        {{-- Plano --}}
                        <div class="max-w-[220px]">
                            <flux:label class="text-xs">{{ __('exercise.exercise_plane') }}</flux:label>
                            <flux:select wire:model.live="exercise_plane_id" size="sm" class="w-full">
                                <option value="">{{ __('site.select') }}</option>

                                @foreach ($planes as $pl)
                                    <option value="{{ $pl->id }}">{{ $pl->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        {{-- Unilateral / Carga externa --}}
                        <div class="max-w-[160px]">
                            <flux:label class="text-xs">{{ __('exercise.unilateral') }}</flux:label>
                            <flux:select wire:model.live="unilateral" size="sm" class="w-full">
                                <option value="">{{ __('site.select') }}</option>

                                <option value="1">{{ __('exercise.yes') }}</option>
                                <option value="0">{{ __('exercise.no') }}</option>
                            </flux:select>
                        </div>
                        <div class="max-w-[180px]">
                            <flux:label class="text-xs">{{ __('exercise.external_load') }}</flux:label>
                            <flux:select wire:model.live="external_load" size="sm" class="w-full">
                                <option value="">{{ __('site.select') }}</option>

                                <option value="1">{{ __('exercise.yes') }}</option>
                                <option value="0">{{ __('exercise.no') }}</option>
                            </flux:select>
                        </div>

                        {{-- Modalidad por defecto --}}
                        <div class="max-w-[200px]">
                            <flux:label class="text-xs">{{ __('exercise.default_modality') }}</flux:label>
                            <flux:select wire:model.live="default_modality" size="sm" class="w-full">
                                <option value="">{{ __('site.select') }}</option>

                                @php $mods = [\App\Models\Tenant\Exercise\Exercise::MOD_REPS, \App\Models\Tenant\Exercise\Exercise::MOD_TIME, \App\Models\Tenant\Exercise\Exercise::MOD_DISTANCE, \App\Models\Tenant\Exercise\Exercise::MOD_CALORIES, \App\Models\Tenant\Exercise\Exercise::MOD_RPE, \App\Models\Tenant\Exercise\Exercise::MOD_LOAD_ONLY, \App\Models\Tenant\Exercise\Exercise::MOD_TEMPO_ONLY]; @endphp
                                @foreach ($mods as $m)
                                    <option value="{{ $m }}">{{ __("exercise.mod_{$m}") }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>
                </x-slot>

                <x-slot name="head">
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left cursor-pointer"
                        wire:click="sort('name')">
                        <span class="inline-flex items-center gap-1">{{ __('exercise.name') }}
                            @if ($sortBy === 'name')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left cursor-pointer"
                        wire:click="sort('code')">
                        <span class="inline-flex items-center gap-1">{{ __('exercise.code') }}
                            @if ($sortBy === 'code')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">
                        {{ __('exercise.level') }}</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">
                        {{ __('exercise.movement_pattern') }}</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">
                        {{ __('exercise.exercise_plane') }}</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left cursor-pointer"
                        wire:click="sort('unilateral')">
                        <span class="inline-flex items-center gap-1">{{ __('exercise.unilateral') }}
                            @if ($sortBy === 'unilateral')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left cursor-pointer"
                        wire:click="sort('external_load')">
                        <span class="inline-flex items-center gap-1">{{ __('exercise.external_load') }}
                            @if ($sortBy === 'external_load')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">
                        {{ __('exercise.default_modality') }}</th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-end">
                        {{ __('exercise.actions') }}</th>
                </x-slot>

                @forelse ($exercises as $exercise)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium">{{ $exercise->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $exercise->code }}</td>
                        <td class="px-6 py-4 text-sm">{{ $exercise->level?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $exercise->pattern?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $exercise->plane?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm">
                            {{ $exercise->unilateral ? __('exercise.yes') : __('exercise.no') }}</td>
                        <td class="px-6 py-4 text-sm">
                            {{ $exercise->external_load ? __('exercise.yes') : __('exercise.no') }}</td>
                        <td class="px-6 py-4 text-sm">{{ __("exercise.mod_{$exercise->default_modality}") }}</td>
                        <td class="px-6 py-4 text-end text-sm">
                            <span class="inline-flex items-center gap-2">
                                <flux:button wire:navigate size="sm"
                                    href="{{ route('tenant.dashboard.exercise.exercises.edit', $exercise) }}">
                                    {{ __('exercise.edit') }}
                                </flux:button>

                                <flux:modal.trigger name="confirm-delete-exercise">
                                    <flux:button size="sm" variant="ghost" type="button"
                                        wire:click="confirmDelete({{ $exercise->id }})">
                                        {{ __('exercise.delete') }}
                                    </flux:button>
                                </flux:modal.trigger>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                            {{ __('exercise.no_exercise_found') }}</td>
                    </tr>
                @endforelse

                <x-slot name="modal">
                    <flux:modal name="confirm-delete-exercise" class="min-w-[22rem]" x-data
                        @exercise-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-exercise' })">
                        <div class="space-y-6">
                            <flux:heading size="lg">{{ __('exercise.delete_exercise_title') }}</flux:heading>
                            <flux:text class="mt-2">{{ __('exercise.delete_exercise_message') }}</flux:text>
                            <div class="flex gap-2">
                                <flux:spacer />
                                <flux:modal.close>
                                    <flux:button variant="ghost">{{ __('exercise.cancel') }}</flux:button>
                                </flux:modal.close>
                                <flux:button wire:click="delete" variant="danger">{{ __('exercise.confirm_delete') }}
                                </flux:button>
                            </div>
                        </div>
                    </flux:modal>
                </x-slot>
            </x-data-table>
        </section>
    </div>
</div>
