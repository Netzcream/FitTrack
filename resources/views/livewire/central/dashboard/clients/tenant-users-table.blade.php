<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">

        {{-- Header --}}
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('Usuarios del cliente') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">
                        {{ __('Listado de usuarios pertenecientes a este cliente') }}
                    </flux:subheading>
                </div>

                {{-- Botón: Agregar usuario --}}
                <flux:modal.trigger name="tenant-user-create">
                    <flux:button size="sm" icon="plus">
                        {{ __('Agregar usuario') }}
                    </flux:button>
                </flux:modal.trigger>
            </div>

            <flux:separator variant="subtle" />
        </div>


        {{-- Tabla principal --}}
        <section class="w-full">
            <x-data-table :pagination="$users">
                {{-- Filtros opcionales --}}
                <x-slot name="filters">
                    {{-- Si querés un buscador o filtro, iría acá --}}
                </x-slot>

                {{-- Encabezado --}}
                <x-slot name="head">
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('Usuario') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('site.roles') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('Creado') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                        {{ __('Acciones') }}
                    </th>
                </x-slot>

                {{-- Filas --}}
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}"
                        class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition">
                        {{-- Usuario --}}
                        <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            <div class="leading-tight">
                                <div class="font-medium text-gray-900 dark:text-neutral-100">
                                    {{ $user->name }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400">
                                    {{ $user->email }}
                                </div>
                            </div>
                        </td>

                        {{-- Roles --}}
                        <td class="px-6 py-4 text-sm">
                            @php
                                $userRoles = is_array($user->roles ?? null) ? $user->roles : [];
                                $firstRole = $userRoles[0] ?? null;
                                $remainingCount = count($userRoles) - 1;
                            @endphp
                            @if(!empty($firstRole))
                                <div class="flex items-center gap-1 flex-wrap">
                                    {{-- Primer rol --}}
                                    <span class="inline-flex items-center rounded-md bg-lime-50 dark:bg-lime-500/10 px-2 py-1 text-xs font-medium text-lime-700 dark:text-lime-400 ring-1 ring-inset ring-lime-600/20 dark:ring-lime-500/20">
                                        {{ $firstRole }}
                                    </span>

                                    {{-- Badge +X con popover si hay más roles --}}
                                    @if($remainingCount > 0)
                                        <div x-data="{ open: false, toggle() { this.open = !this.open } }" class="relative inline-block">
                                            <span
                                                @mouseenter="open = true"
                                                @mouseleave="open = false"
                                                class="inline-flex items-center rounded-md bg-zinc-50 dark:bg-zinc-500/10 px-2 py-1 text-xs font-medium text-zinc-700 dark:text-zinc-400 ring-1 ring-inset ring-zinc-600/20 dark:ring-zinc-500/20 cursor-help"
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
                                                class="fixed z-[9999] w-48 rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-lg p-3"
                                                style="display: none;"
                                                x-anchor.bottom-start="$refs.trigger"
                                            >
                                                <div class="text-xs font-semibold text-gray-700 dark:text-neutral-300 mb-2">
                                                    {{ __('Otros roles:') }}
                                                </div>
                                                <div class="space-y-1">
                                                    @foreach(array_slice($userRoles, 1) as $additionalRole)
                                                        <div class="text-xs text-gray-600 dark:text-neutral-400 flex items-center gap-1">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-lime-500"></span>
                                                            {{ $additionalRole }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400 dark:text-neutral-500 text-xs">{{ __('Sin roles') }}</span>
                            @endif
                        </td>

                        {{-- Creado --}}
                        <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $user->created_at_formatted ?? '-' }}
                        </td>

                        {{-- Acciones --}}
                        <td class="px-6 py-4 text-end text-sm font-medium space-x-2">
                            <flux:modal.trigger name="assign-roles">
                                <flux:button size="sm" variant="outline"
                                    wire:click="openRolesModal({{ $user->id }})">
                                    {{ __('Asignar roles') }}
                                </flux:button>
                            </flux:modal.trigger>

                            <flux:modal.trigger name="tenant-user-reset-password">
                                <flux:button size="sm" variant="ghost"
                                    wire:click="openResetModal({{ $user->id }})">
                                    {{ __('Resetear Password') }}
                                </flux:button>
                            </flux:modal.trigger>

                            <flux:button size="sm" variant="ghost" wire:click="impersonate({{ $user->id }})">
                                {{ __('Ingresar como') }}
                            </flux:button>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                            {{ __('Aún no hay usuarios.') }}
                        </td>
                    </tr>
                @endforelse

                {{-- Modales --}}
                <x-slot name="modal">
                    {{-- Modal reset password --}}
                    <flux:modal name="tenant-user-reset-password" class="min-w-[22rem]">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('Resetear Password') }}</flux:heading>
                                <flux:text class="mt-2">
                                    {{ __('Ingresá y confirmá la nueva contraseña del usuario seleccionado.') }}
                                </flux:text>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <flux:label for="pwd">{{ __('Nueva contraseña') }}</flux:label>
                                    <input id="pwd" type="password" wire:model.defer="password"
                                        class="mt-2 w-full rounded-lg border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <flux:label for="pwd2">{{ __('Confirmar contraseña') }}</flux:label>
                                    <input id="pwd2" type="password" wire:model.defer="password_confirmation"
                                        class="mt-2 w-full rounded-lg border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                                </div>
                            </div>

                            <div class="flex gap-2 justify-end">
                                <flux:modal.close>
                                    <flux:button variant="ghost" size="sm">{{ __('Cancelar') }}</flux:button>
                                </flux:modal.close>
                                <flux:button wire:click="saveResetPassword" size="sm" icon="key">
                                    {{ __('Guardar') }}
                                </flux:button>
                            </div>
                        </div>
                    </flux:modal>

                    {{-- Modal: Asignar roles --}}
                    <flux:modal name="assign-roles" class="min-w-[28rem]">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('Asignar roles') }}</flux:heading>
                                <flux:text class="mt-2">
                                    {{ __('Seleccioná los roles que deseas asignar a este usuario.') }}
                                </flux:text>
                            </div>

                            <div class="space-y-3">
                                @forelse($availableRoles as $role)
                                    <flux:checkbox
                                        wire:model="selectedRoles"
                                        value="{{ $role }}"
                                        :label="$role"
                                    />
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-neutral-400">
                                        {{ __('No hay roles disponibles.') }}
                                    </p>
                                @endforelse
                            </div>

                            <div class="flex gap-2 justify-end">
                                <flux:modal.close>
                                    <flux:button variant="ghost" size="sm">{{ __('Cancelar') }}</flux:button>
                                </flux:modal.close>
                                <flux:button wire:click="saveRoles" size="sm" icon="user-group">
                                    {{ __('Guardar') }}
                                </flux:button>
                            </div>
                        </div>
                    </flux:modal>

                    {{-- Modal: Crear usuario --}}
                    <flux:modal name="tenant-user-create" class="min-w-[28rem]">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('Agregar nuevo usuario') }}</flux:heading>
                                <flux:text class="mt-2">
                                    {{ __('Completá los datos para crear un nuevo usuario.') }}
                                </flux:text>
                            </div>

                            <div class="space-y-4">
                                <flux:input wire:model.defer="new_name" :label="__('Nombre completo')"
                                    placeholder="Ej: Juan Pérez" />
                                <flux:input wire:model.defer="new_email" :label="__('Email')"
                                    placeholder="Ej: juan@example.com" />
                                <flux:input wire:model.defer="new_password" :label="__('Contraseña')" type="password" />
                            </div>

                            <div class="flex gap-2 justify-end">
                                <flux:modal.close>
                                    <flux:button variant="ghost" size="sm">{{ __('Cancelar') }}</flux:button>
                                </flux:modal.close>
                                <flux:button wire:click="saveNewUser" size="sm" icon="plus">
                                    {{ __('Guardar') }}
                                </flux:button>
                            </div>
                        </div>
                    </flux:modal>


                </x-slot>
            </x-data-table>
        </section>
    </div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-url', e => window.open(e.url, '_blank'));
        });
        document.addEventListener('livewire:navigated', () => {
            Livewire.on('open-url', e => window.open(e.url, '_blank'));
        });
    </script>


</div>
