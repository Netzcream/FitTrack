<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">

        {{-- HEADER --}}
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('exercises.index_title') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">{{ __('exercises.index_subheading') }}
                    </flux:subheading>
                </div>

                <flux:button as="a" href="{{ route('tenant.dashboard.exercises.create') }}" variant="primary"
                    icon="plus">
                    {{ __('exercises.new_exercise') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        {{-- TABLA PRINCIPAL --}}
        <section class="w-full">
            <x-data-table :pagination="$exercises">

                {{-- 🔹 FILTROS --}}
                <x-slot name="filters">
                    <x-index-filters :searchPlaceholder="__('exercises.search_placeholder')">
                        <x-slot name="additionalFilters">
                            {{-- Filtro por status --}}
                            <div class="min-w-[160px]">
                                <flux:select size="sm" wire:model.live="status" :label="__('common.status')">
                                    <option value="">{{ __('common.all') }}</option>
                                    <option value="1">{{ __('common.active') }}</option>
                                    <option value="0">{{ __('common.inactive') }}</option>
                                </flux:select>
                            </div>
                        </x-slot>
                    </x-index-filters>
                </x-slot>

                {{-- 🔹 HEAD --}}
                <x-slot name="head">

                    <th wire:click="sort('name')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        <span class="inline-flex items-center gap-1">
                            {{ __('exercises.name') }}
                            @if ($sortBy === 'name')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>

                    <th wire:click="sort('level')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        {{ __('exercises.level') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('exercises.equipment') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-center">
                        {{ __('common.status') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                        {{ __('common.actions') }}
                    </th>
                </x-slot>

                {{-- 🔹 BODY --}}
                @forelse ($exercises as $exercise)
                    <tr wire:key="exercise-{{ $exercise->uuid }}"
                        class="divide-y divide-gray-200 dark:divide-neutral-700">
                        <td class="align-top px-3 py-4">
                            <div class="inline-flex items-center gap-3">
                                <div
                                    class="h-10 w-10 rounded-lg overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center">
                                    @php
                                        $media = $exercise->getFirstMedia('images');
                                    @endphp

                                    @if ($media)
                                        @php
                                            $thumbUrl = $media->hasGeneratedConversion('thumb')
                                                ? $media->getUrl('thumb')
                                                : $media->getUrl();
                                        @endphp
                                        <img src="{{ $thumbUrl }}" alt="{{ $exercise->name }}"
                                            class="object-cover h-full w-full" />
                                    @else
                                        <x-icons.lucide.image class="h-5 w-5 text-gray-400 dark:text-neutral-500" />
                                    @endif
                                </div>

                                <div class="leading-tight">
                                    <div class="font-medium text-gray-900 dark:text-neutral-100">
                                        {{ $exercise->name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-neutral-400 capitalize">
                                        {{ $exercise->category ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>




                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200 capitalize">
                            {{ $exercise->level ?? '—' }}
                        </td>

                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $exercise->equipment ?? '—' }}
                        </td>

                        <td class="align-top px-6 py-4 text-sm text-center">
                            @php
                                $state = $exercise->is_active ? 'active' : 'inactive';
                                $styles = [
                                    'active' =>
                                        'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/40 dark:text-green-300 dark:ring-green-900',
                                    'inactive' =>
                                        'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800',
                                ];
                            @endphp
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $styles[$state] }}">
                                {{ __('common.' . $state) }}
                            </span>
                        </td>

                        <td class="align-top px-6 py-4 text-end text-sm font-medium">
                            <span
                                class="inline-flex items-center gap-2 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">
                                <flux:button size="sm" as="a" wire:navigate
                                    href="{{ route('tenant.dashboard.exercises.edit', $exercise->uuid) }}">
                                    {{ __('common.edit') }}
                                </flux:button>

                                <flux:modal.trigger name="confirm-delete-exercise">
                                    <flux:button size="sm" variant="ghost"
                                        wire:click="confirmDelete('{{ $exercise->uuid }}')">
                                        {{ __('common.delete') }}
                                    </flux:button>
                                </flux:modal.trigger>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                            {{ __('common.empty_state') }}
                        </td>
                    </tr>
                @endforelse

                {{-- 🔹 MODAL --}}
                <x-slot name="modal">
                    <flux:modal name="confirm-delete-exercise" class="min-w-[22rem]" x-data
                        @exercise-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-exercise' })">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('common.delete_title') }}</flux:heading>
                                <flux:text class="mt-2">{{ __('common.delete_msg') }}</flux:text>
                            </div>
                            <div class="flex gap-2">
                                <flux:spacer />
                                <flux:modal.close>
                                    <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                                </flux:modal.close>
                                <flux:button wire:click="delete" variant="danger">
                                    {{ __('common.confirm_delete') }}
                                </flux:button>
                            </div>
                        </div>
                    </flux:modal>
                </x-slot>

            </x-data-table>
        </section>
    </div>
</div>
