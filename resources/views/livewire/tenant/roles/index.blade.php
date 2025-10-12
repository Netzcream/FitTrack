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
                        <div class="flex flex-wrap gap-4 w-full items-end">
                            <!-- Buscador -->
                            <div class="max-w-[260px] flex-1">
                                <flux:input size="sm" class="w-full" wire:model.live.debounce.250ms="search" :label="__('common.search')"
                                    placeholder="{{ __('roles.search_placeholder') }}" />
                            </div>

                            <!-- Select de permiso -->
                            <div class="min-w-[200px]">

                                <flux:select size="sm" wire:model.live="permission" :label="__('roles.permission_filter')">
                                    <option value="">{{ __('common.all') }}</option>
                                    @foreach ($permissions as $r)
                                        <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                                    @endforeach
                                </flux:select>


                            </div>
                            <div>
                                <flux:button size="sm" variant="ghost" wire:click="resetFilters">
                                    {{ __('common.clear') }}</flux:button>
                            </div>

                        </div>
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
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                {{ $role->name }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                                @foreach ($role->permissions as $permission)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold mr-1">{{ ucfirst($permission->name) }}</span>
                                @endforeach
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
