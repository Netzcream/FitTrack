<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6">
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('exercise.muscles') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">
                        {{ __('exercise.muscles_subheading') }}
                    </flux:subheading>
                </div>
                <flux:button as="a" href="{{ route('tenant.dashboard.exercise.muscles.create') }}"
                    variant="primary" icon="plus">
                    {{ __('exercise.new_muscle') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        <section class="w-full">
            @php /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $muscles */ @endphp
            <x-data-table :pagination="$muscles">
                <x-slot name="filters">
                    <div class="flex flex-wrap gap-4 w-full items-end">
                        {{-- Búsqueda --}}
                        <div class="max-w-[260px] flex-1">
                            <flux:label class="text-xs">{{ __('exercise.search') }}</flux:label>
                            <flux:input size="sm" wire:model.live.debounce.400ms="search"
                                placeholder="{{ __('exercise.search_placeholder') }}" class="w-full" />
                        </div>

                        {{-- Filtro estado (option vacío) --}}
                        <div class="max-w-[220px]">
                            <flux:label class="text-xs">{{ __('exercise.status') }}</flux:label>
                            <flux:select wire:model.live="status" size="sm" class="w-full">
                                <option value="">{{__('site.select')}}</option>
                                <option value="{{ \App\Models\Tenant\Exercise\Muscle::STATUS_DRAFT }}">
                                    {{ __('exercise.status_draft') }}
                                </option>
                                <option value="{{ \App\Models\Tenant\Exercise\Muscle::STATUS_PUBLISHED }}">
                                    {{ __('exercise.status_published') }}
                                </option>
                                <option value="{{ \App\Models\Tenant\Exercise\Muscle::STATUS_ARCHIVED }}">
                                    {{ __('exercise.status_archived') }}
                                </option>
                            </flux:select>
                        </div>

                        {{-- Filtro por grupo (option vacío) --}}
                        <div class="max-w-[240px]">
                            <flux:label class="text-xs">{{ __('exercise.muscle_group') }}</flux:label>
                            <flux:select wire:model.live="muscle_group_id" size="sm" class="w-full">
                                <option value="">{{__('site.select')}}</option>
                                @foreach ($groupOptions as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>
                </x-slot>

                <x-slot name="head">
                    {{-- Orden --}}
                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 text-left">
                        {{ __('exercise.order') }}
                    </th>
                    {{-- Nombre --}}
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 text-left">
                        {{ __('exercise.name') }}
                    </th>
                    {{-- Código --}}
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 text-left">
                        {{ __('exercise.code') }}
                    </th>
                    {{-- Grupo --}}
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 text-left">
                        {{ __('exercise.muscle_group') }}
                    </th>
                    {{-- Status --}}
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 text-left">
                        {{ __('exercise.status') }}
                    </th>
                    {{-- Acciones --}}
                    <th class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        {{ __('exercise.actions') }}
                    </th>
                </x-slot>

                @forelse ($muscles as $muscle)
                    <tr>
                        {{-- Flechas en columna, sin número --}}
                        <td class="align-top px-4 py-4 text-sm">
                            <div class="flex flex-col items-start gap-1">
                                <flux:button size="xs" variant="ghost" icon="chevron-up"
                                    title="{{ __('exercise.move_up') }}"
                                    wire:click="moveUp({{ $muscle->id }})"
                                    :disabled="($muscle->order === null) || ($muscle->order <= $minOrder)" />

                                <flux:button size="xs" variant="ghost" icon="chevron-down"
                                    title="{{ __('exercise.move_down') }}"
                                    wire:click="moveDown({{ $muscle->id }})"
                                    :disabled="($muscle->order === null) || ($muscle->order >= $maxOrder)" />
                            </div>
                        </td>

                        <td class="align-top px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
                            {{ $muscle->name }}
                        </td>

                        <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                            {{ $muscle->code }}
                        </td>

                        <td class="align-top px-6 py-4 text-sm text-gray-700 dark:text-neutral-300">
                            {{ $muscle->group?->name ?? '—' }}
                        </td>

                        <td class="align-top px-6 py-4 text-sm">
                            @php
                                $status = $muscle->status;
                                $badgeClass = match ($status) {
                                    \App\Models\Tenant\Exercise\Muscle::STATUS_PUBLISHED => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                    \App\Models\Tenant\Exercise\Muscle::STATUS_ARCHIVED  => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-neutral-800 dark:text-neutral-300',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded text-xs {{ $badgeClass }}">
                                {{ __("exercise.status_{$status}") }}
                            </span>
                        </td>

                        <td class="align-top px-6 py-4 text-end text-sm font-medium">
                            <span class="text-xs text-gray-400 dark:text-neutral-500 inline-flex items-center whitespace-nowrap gap-2">
                                <flux:button wire:navigate size="sm"
                                    href="{{ route('tenant.dashboard.exercise.muscles.edit', $muscle) }}">
                                    {{ __('exercise.edit') }}
                                </flux:button>

                                <flux:modal.trigger name="confirm-delete-muscle">
                                    <flux:button size="sm" variant="ghost" type="button"
                                        wire:click="confirmDelete({{ $muscle->id }})">
                                        {{ __('exercise.delete') }}
                                    </flux:button>
                                </flux:modal.trigger>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                            {{ __('exercise.no_muscle_found') }}
                        </td>
                    </tr>
                @endforelse

                <x-slot name="modal">
                    <flux:modal name="confirm-delete-muscle" class="min-w-[22rem]" x-data
                        @muscle-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-muscle' })">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('exercise.delete_muscle_title') }}</flux:heading>
                                <flux:text class="mt-2">
                                    {{ __('exercise.delete_muscle_message') }}
                                </flux:text>
                            </div>
                            <div class="flex gap-2">
                                <flux:spacer />
                                <flux:modal.close>
                                    <flux:button variant="ghost">{{ __('exercise.cancel') }}</flux:button>
                                </flux:modal.close>
                                <flux:button wire:click="delete" variant="danger">
                                    {{ __('exercise.confirm_delete') }}
                                </flux:button>
                            </div>
                        </div>
                    </flux:modal>
                </x-slot>
            </x-data-table>
        </section>
    </div>
</div>
