<div class="mb-4 w-full max-w-sm">
    <flux:input wire:keyup.debounce.500ms="filter()" wire:model.debounce.500ms="search" label="{{ __('Buscar') }}" />
</div>


<div class="flex flex-col">
    <div class="-m-1.5 overflow-x-auto">
        <div class="p-1.5 min-w-full inline-block align-middle">
            <div class="overflow-hidden border border-zinc-700 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                    <thead>
                        <tr>
                            <th
                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer">
                                {{ __('Nombre') }}

                            </th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer"
                                wire:click="sort('id')">
                                {{ __('Dominio') }}
                                @if ($sortBy === 'id')
                                    {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                                @endif
                            </th>
                            <th
                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                {{ __('DDBB') }}
                            </th>
                            <th
                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                {{ __('Status') }}
                            </th>
                            <th
                                class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                {{ __('SSL') }}
                            </th>

                            <th
                                class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                {{ __('site.actions') }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                        @forelse ($this->clients as $client)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                    {{ $client->name }}</td>

                                <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                                    <a href="//{{ $client->domains->first()->domain ?? '' }}" target="_blank"
                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">{{ $client->domains->first()->domain ?? '—' }}</a>

                                    @if (count($client->domains) > 1)
                                        <span
                                            class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">
                                            +{{ count($client->domains) - 1 }}
                                        </span>
                                    @endif


                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                                    {{ $client->tenancy_db_name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                                    {{ $client->status->label() ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                                    @php
                                        $domain = $client->domains?->first()?->domain;
                                        //$domain = "lunigo.com";
                                    @endphp
                                    @if (\App\Models\Tenant::hasValidSslFor($domain))
                                        ✅ Válido hasta
                                        {{ \App\Models\Tenant::sslExpirationDateFor($domain)?->format('Y-m-d') }}
                                    @else
                                        ❌ Vencido o no instalado
                                    @endif


                                </td>
                                <td class="px-6 py-4 text-end text-sm font-medium">
                                    <a wire:navigate href="{{ route('central.dashboard.clients.edit', $client) }}"
                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                        {{ __('Editar') }}
                                    </a>


                                    @if ($client->status == App\Enums\TenantStatus::DELETED)
                                        <flux:modal.trigger name="confirm-delete-client-force">
                                            <button wire:click="confirmDeleteForce('{{ $client->id }}')"
                                                class="cursor-pointer ml-2 text-sm font-medium text-red-600 dark:text-red-500 hover:underline">
                                                {{ __('ELIMINAR DEFINITIVAMENTE') }}
                                            </button>
                                        </flux:modal.trigger>
                                    @else
                                        <flux:modal.trigger name="confirm-delete-client">
                                            <button wire:click="confirmDelete('{{ $client->id }}')"
                                                class="cursor-pointer ml-2 text-sm font-medium text-red-600 dark:text-red-500 hover:underline">
                                                {{ __('Eliminar') }}
                                            </button>
                                        </flux:modal.trigger>
                                    @endif





                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                                    {{ __('No hay clientes cargados.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $this->clients->links() }}
    </div>





    <flux:modal name="confirm-delete-client-force" class="min-w-[22rem]" x-data
        @contact-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-client-force' })">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Eliminar cliente definitivamente?') }}</flux:heading>

                <flux:text class="mt-2">
                    {{ __('Esta acción ELIMINARÁ el cliente DEFINITIVAMENTE y no podrá deshacerse. ¿Estás seguro?') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button wire:click="deleteForce" variant="danger">
                    {{ __('Sí, eliminar') }}
                </flux:button>

            </div>
        </div>
    </flux:modal>


    <flux:modal name="confirm-delete-client" class="min-w-[22rem]" x-data
        @contact-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-client' })">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Eliminar cliente?') }}</flux:heading>

                <flux:text class="mt-2">
                    {{ __('Esta acción eliminará el cliente. ¿Estás seguro?') }}
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




</div>
