<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">
        {{-- Header --}}
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('commercial_plans.index_title') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">{{ __('commercial_plans.index_subheading') }}
                    </flux:subheading>
                </div>

                <flux:button as="a" href="{{ route('tenant.dashboard.commercial-plans.create') }}"
                    variant="primary" icon="plus">
                    {{ __('commercial_plans.new_plan') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        {{-- Tabla principal --}}
        <section class="w-full">
            <x-data-table :pagination="$plans">

                {{-- Filtros --}}
                <x-slot name="filters">
                    <x-index-filters :searchPlaceholder="__('commercial_plans.search_placeholder')">
                        <x-slot name="additionalFilters">
                            {{-- Estado --}}
                            <div class="min-w-[150px]">
                                <flux:select size="sm" :label="__('common.status')" wire:model.live="status">
                                    <option value="">{{ __('common.all') }}</option>
                                    <option value="1">{{ __('common.active') }}</option>
                                    <option value="0">{{ __('common.inactive') }}</option>
                                </flux:select>
                            </div>
                        </x-slot>
                    </x-index-filters>
                </x-slot>

                {{-- Encabezados --}}
                <x-slot name="head">
                    <th
                        class="px-3 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left w-12">
                        {{ __('common.order') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('commercial_plans.name') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('commercial_plans.description') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-center">
                        {{ __('common.status') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                        {{ __('common.actions') }}
                    </th>
                </x-slot>

                {{-- Filas --}}
                @forelse ($plans as $plan)
                    <tr class="divide-y divide-gray-200 dark:divide-neutral-700">
                        <td class="align-top px-3 py-3 text-sm w-8">
                            <div class="flex flex-col items-center leading-none">
                                <a wire:click.prevent="moveUp({{ $plan->id }})" title="{{ __('common.move_up') }}"
                                    class="cursor-pointer text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300">
                                    <x-icons.lucide.chevron-up class="h-4 w-4" />
                                </a>

                                <a wire:click.prevent="moveDown({{ $plan->id }})"
                                    title="{{ __('common.move_down') }}"
                                    class="cursor-pointer text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300">
                                    <x-icons.lucide.chevron-down class="h-4 w-4" />
                                </a>
                            </div>
                        </td>


                        {{-- Nombre --}}
                        <td class="align-top px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
                            {{ $plan->name }}
                        </td>

                        {{-- Descripción breve --}}
                        <td
                            class="align-top px-6 py-4 text-sm text-gray-700 dark:text-neutral-300 truncate max-w-[280px]">
                            {{ Illuminate\Support\Str::limit($plan->description, 80) ?: '-' }}
                        </td>

                        {{-- Estado --}}
                        <td class="align-top px-6 py-4 text-center text-sm">
                            @php
                                $state = $plan->is_active ? 'active' : 'inactive';
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

                        {{-- Acciones --}}
                        <td class="align-top px-6 py-4 text-end text-sm font-medium">
                            <span
                                class="inline-flex items-center gap-2 space-x-1 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">

                                <flux:button size="sm" as="a" wire:navigate
                                    href="{{ route('tenant.dashboard.commercial-plans.edit', $plan) }}">
                                    {{ __('common.edit') }}
                                </flux:button>

                                <flux:modal.trigger name="confirm-delete-commercial-plan">
                                    <flux:button size="sm" variant="ghost" wire:click="confirmDelete({{ $plan->id }})">
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

                {{-- Modal único de confirmación --}}
                <x-slot name="modal">
                    <flux:modal name="confirm-delete-commercial-plan" class="min-w-[22rem]" x-data
                        @plan-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-commercial-plan' })">
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
                                <flux:button variant="danger" wire:click="delete">
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
