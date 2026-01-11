<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6">

        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('roles.index_title') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">
                        {{ __('Listado de roles registrados en el sistema.') }}
                    </flux:subheading>
                </div>
                <flux:button as="a" href="{{ route('tenant.dashboard.roles.create') }}" variant="primary"
                    icon="plus">
                    {{ __('Nuevo rol') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        <div class="mt-5 w-full ">
            <section class="w-full">
                <x-data-table :pagination="$roles">
                    <x-slot name="filters">
                        <x-index-filters :searchPlaceholder="__('roles.search_placeholder')">
                            <x-slot name="additionalFilters">
                                {{-- Select de permiso --}}
                                <div class="min-w-[200px]">
                                    <flux:select size="sm" wire:model.live="permission" :label="__('roles.permission_filter')">
                                        <option value="">{{ __('common.all') }}</option>
                                        @foreach ($permissions as $r)
                                            <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                                        @endforeach
                                    </flux:select>
                                </div>
                            </x-slot>
                        </x-index-filters>
                    </x-slot>


                    <x-slot name="head">
                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer"
                            wire:click="sort('name')">
                            <span class="inline-flex items-center gap-1 whitespace-nowrap cursor-pointer">
                                {{ __('Nombre') }}
                                @if ($sortBy === 'name')
                                    {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                                @endif
                            </span>
                        </th>
                        <th
                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                            {{ __('Permisos') }}
                        </th>
                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer"
                            wire:click="sort('created_at')">
                            {{ __('Alta') }}
                        </th>
                        <th
                            class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                            {{ __('Acciones') }}
                        </th>

                    </x-slot>

                    @forelse ($roles as $role)
                        <tr wire:key="role-{{ $role->id }}">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                {{ $role->name }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                                @php
                                    $permissions = $role->permissions;
                                    $visibleCount = 2;
                                    $totalCount = $permissions->count();
                                    $remainingCount = $totalCount - $visibleCount;
                                @endphp

                                <div class="inline-flex items-center gap-1 flex-wrap">
                                    {{-- Primeros 2 badges --}}
                                    @foreach ($permissions->take($visibleCount) as $permission)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium text-white"
                                            style="background-color: var(--ftt-color-base);">
                                            {{ ucfirst($permission->name) }}
                                        </span>
                                    @endforeach

                                    {{-- Badge +X con popover si hay más permisos --}}
                                    @if ($remainingCount > 0)
                                        <div x-data="{ open: false }" class="relative inline-block">
                                            <span
                                                @mouseenter="open = true"
                                                @mouseleave="open = false"
                                                class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800 cursor-help"
                                                x-ref="trigger"
                                            >
                                                +{{ $remainingCount }}
                                            </span>

                                            {{-- Popover con posicionamiento fijo --}}
                                            <div
                                                x-show="open"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-150"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                @mouseenter="open = true"
                                                @mouseleave="open = false"
                                                class="fixed z-[9999] w-56 rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-lg p-3"
                                                style="display: none;"
                                                x-anchor.bottom-start="$refs.trigger"
                                            >
                                                <div class="text-xs font-semibold text-gray-700 dark:text-neutral-300 mb-2">
                                                    {{ __('Todos los permisos') }} ({{ $totalCount }})
                                                </div>
                                                <div class="space-y-1 max-h-48 overflow-y-auto">
                                                    @foreach ($permissions as $permission)
                                                        <div class="text-xs text-gray-600 dark:text-neutral-400 flex items-center gap-1">
                                                            <span class="w-1.5 h-1.5 rounded-full" style="background-color: var(--ftt-color-base);"></span>
                                                            {{ ucfirst($permission->name) }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                                {{ $role->created_at ? $role->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-end text-sm font-medium">
                                <span
                                    class="text-xs text-gray-400 dark:text-neutral-500 inline-flex items-center whitespace-nowrap space-x-1">

                                    <flux:button size="sm" as="a" wire:navigate
                                        href="{{ route('tenant.dashboard.roles.edit', $role->id) }}">
                                        {{ __('Editar') }}
                                    </flux:button>

                                    <flux:modal.trigger name="confirm-delete-role">
                                        <flux:button size="sm" wire:click="confirmDelete('{{ $role->id }}')"
                                            variant="ghost">
                                            {{ __('Eliminar') }}
                                        </flux:button>
                                    </flux:modal.trigger>

                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                                {{ __('No hay roles registrados.') }}
                            </td>
                        </tr>
                    @endforelse

                    <x-slot name="modal">
                        <flux:modal name="confirm-delete-role" class="min-w-[22rem]" x-data
                            @role-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-role' })">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">{{ __('¿Eliminar rol?') }}</flux:heading>
                                    <flux:text class="mt-2">
                                        {{ __('Esta acción eliminará el rol seleccionado. ¿Estás seguro?') }}
                                    </flux:text>
                                </div>
                                <div class="flex gap-2">
                                    <flux:spacer />
                                    <flux:modal.close>
                                        <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                                    </flux:modal.close>
                                    <flux:button wire:click="delete" variant="danger">
                                        {{ __('Sí, eliminar') }}
                                    </flux:button>
                                </div>
                            </div>
                        </flux:modal>
                    </x-slot>

                </x-data-table>
            </section>
        </div>
    </div>
</div>
