<div class="rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden bg-white dark:bg-neutral-900">
    <div class="p-4 border-b border-neutral-200 dark:border-neutral-700">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg" level="2">{{ __('Usuarios del cliente') }}</flux:heading>
                <flux:subheading size="md" class="mt-1">
                    {{ __('Listado de usuarios pertenecientes a este cliente') }}
                </flux:subheading>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
            <thead class="bg-neutral-50 dark:bg-neutral-900/40">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-start text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">
                        {{ __('Nombre') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">
                        {{ __('Email') }}
                    </th>

                    <th scope="col"
                        class="px-6 py-3 text-start text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">
                        {{ __('Creado') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-300">
                        {{ __('Actions') }}
                    </th>

                </tr>
            </thead>

            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @if ($users && $users->count())
                    @foreach ($users as $user)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-800 dark:text-neutral-200">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-800 dark:text-neutral-200">
                                {{ $user->email }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-800 dark:text-neutral-200">
                                {{ $user->created_at_formatted ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-800 dark:text-neutral-200">
                                <div class="flex items-center gap-2">

                                    {{-- Hacer Administrador (abre confirmación) --}}
                                    <flux:modal.trigger name="confirm-make-admin">
                                        <flux:button size="xs" variant="outline"
                                            wire:click="confirmMakeAdmin({{ $user->id }})">
                                            {{ __('Hacer Administrador') }}
                                        </flux:button>
                                    </flux:modal.trigger>

                                    {{-- Resetear Password (abre modal reset) --}}
                                    <flux:modal.trigger name="tenant-user-reset-password">
                                        <flux:button size="xs" variant="ghost"
                                            wire:click="openResetModal({{ $user->id }})">
                                            {{ __('Resetear Password') }}
                                        </flux:button>
                                    </flux:modal.trigger>

                                </div>
                            </td>



                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4"
                            class="px-6 py-10 text-center text-sm text-neutral-500 dark:text-neutral-400">
                            {{ __('Aún no hay usuarios en este tenant.') }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="p-4">
        @if ($users)
            {{ $users->onEachSide(1)->links(data: ['scrollTo' => false]) }}
        @endif
    </div>


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
                        class="mt-2 w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <flux:label for="pwd2">{{ __('Confirmar contraseña') }}</flux:label>
                    <input id="pwd2" type="password" wire:model.defer="password_confirmation"
                        class="mt-2 w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button wire:click="saveResetPassword" icon="key">
                    {{ __('Guardar') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="confirm-make-admin" class="min-w-[22rem]" x-data
        @admin-made.window="$dispatch('modal-close', { name: 'confirm-make-admin' })">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Convertir en Administrador?') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Esta acción quitará todos los roles existentes del usuario y dejará únicamente el rol Admin.') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button wire:click="makeAdmin" variant="danger">
                    {{ __('Sí, confirmar') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>


</div>
