<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6">

        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('Usuarios') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">
                        {{ __('Listado de usuarios registrados en el sistema.') }}
                    </flux:subheading>
                </div>
                <flux:button as="a" href="{{ route('tenant.dashboard.users.create') }}" variant="primary" icon="plus">
                    {{ __('Nuevo usuario') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        <div class="mt-5 w-full ">
            <section class="w-full">




                <x-data-table :pagination="$users">
                    <x-slot name="filters">
                        <div class="max-w-[210px] flex-1">
                            <flux:input wire:model.live.debounce.400ms="search"
                                placeholder="Buscar nombre, email, rol..." size="sm" class="w-full" />
                        </div>
                        <div class="w-[250px]">
                            <flux:select wire:model.live="role" size="sm" class="w-full">
                                <flux:select.option value="">{{ __('Todos los roles') }}
                                </flux:select.option>
                                @foreach ($roles as $r)
                                    <flux:select.option value="{{ $r }}">{{ ucfirst($r) }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
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
                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer"
                            wire:click="sort('email')">
                            {{ __('Email') }}
                        </th>
                        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer"
                            wire:click="sort('role')">
                            {{ __('Rol') }}
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

                    @forelse ($users as $user)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                                @foreach ($user->roles as $role)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold mr-1">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                                {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-end text-sm font-medium">
                                <span
                                    class="text-xs text-gray-400 dark:text-neutral-500 inline-flex items-center whitespace-nowrap">

                                    <a wire:navigate href="{{ route('tenant.dashboard.users.edit', $user->id) }}"
                                        class="ml-2 inline-flex items-center gap-x-2 text-sm  rounded-lg text-green-600 hover:text-green-800 dark:text-green-500 dark:hover:text-green-400">
                                        {{ __('Editar') }}
                                    </a>
                                    @if (auth()->user()->id !== $user->id)
                                        <flux:modal.trigger name="confirm-delete-user">
                                            <button wire:click="confirmDelete('{{ $user->id }}')"
                                                class="cursor-pointer ml-2 inline-flex items-center gap-x-2 text-sm  rounded-lg text-red-600 hover:text-red-800 dark:text-red-500 dark:hover:text-red-400">
                                                {{ __('Eliminar') }}
                                            </button>
                                        </flux:modal.trigger>
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                                {{ __('No hay usuarios registrados.') }}
                            </td>
                        </tr>
                    @endforelse

                    <x-slot name="modal">
                        <flux:modal name="confirm-delete-user" class="min-w-[22rem]" x-data
                            @user-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-user' })">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">{{ __('¿Eliminar usuario?') }}</flux:heading>
                                    <flux:text class="mt-2">
                                        {{ __('Esta acción eliminará el usuario seleccionado. ¿Estás seguro?') }}
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
