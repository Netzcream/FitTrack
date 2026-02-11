<section class="w-full">
    <x-data-table :pagination="$this->clients">
        {{-- Filtros --}}
        <x-slot name="filters">
            <x-index-filters :searchPlaceholder="__('Buscar por nombre o dominio...')" />
        </x-slot>

        {{-- Cabecera --}}
        <x-slot name="head">
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                {{ __('Nombre') }}
            </th>
            <th wire:click="sort('id')"
                class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                <span class="inline-flex items-center gap-1">
                    {{ __('Dominio') }}
                    @if ($sortBy === 'id')
                        {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                    @endif
                </span>
            </th>
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                {{ __('Plan') }}
            </th>
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                {{ __('DDBB') }}
            </th>
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                {{ __('Status') }}
            </th>
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                {{ __('SSL') }}
            </th>
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                {{ __('site.actions') }}
            </th>
        </x-slot>

        {{-- Filas --}}
        @forelse ($this->clients as $client)
            <tr wire:key="client-{{ $client->id }}">
                <td class="align-top px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
                    {{ $client->name }}
                </td>

                <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                    <a href="//{{ $client->domains->first()->domain ?? '' }}" target="_blank"
                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                        {{ $client->domains->first()->domain ?? '-' }}
                    </a>

                    @if (count($client->domains) > 1)
                        <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                            +{{ count($client->domains) - 1 }}
                        </span>
                    @endif
                </td>

                <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                    {{ $client->plan?->name ?? '-' }}
                </td>

                <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                    {{ $client->tenancy_db_name ?? '-' }}
                </td>

                <td class="align-top px-6 py-4 text-sm">
                    @php
                        $state = $client->status->value ?? '';
                        $styles = [
                            'active'   => 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/40 dark:text-green-300 dark:ring-green-900',
                            'paused'   => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-900',
                            'inactive' => 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800',
                            'deleted'  => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $styles[$state] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800' }}">
                        {{ $client->status->label() }}
                    </span>
                </td>

                <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                    @php
                        $domain = $client->domains?->first()?->domain;
                    @endphp
                    @if (\App\Models\Tenant::hasValidSslFor($domain))
                        <span class="text-green-600 dark:text-green-400">
                            ✅ Válido hasta {{ \App\Models\Tenant::sslExpirationDateFor($domain)?->format('Y-m-d') }}
                        </span>
                    @else
                        <span class="text-red-600 dark:text-red-400">
                            ❌ Vencido o no instalado
                        </span>
                    @endif
                </td>

                <td class="align-top px-6 py-4 text-end text-sm font-medium">
                    <span class="inline-flex items-center gap-2 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">
                        <flux:button size="sm" as="a" wire:navigate href="{{ route('central.dashboard.clients.edit', $client) }}">
                            {{ __('Editar') }}
                        </flux:button>

                        @if ($client->status == App\Enums\TenantStatus::DELETED)
                            <flux:modal.trigger name="confirm-delete-client-force">
                                <flux:button size="sm" variant="danger" wire:click="confirmDeleteForce('{{ $client->id }}')">
                                    {{ __('ELIMINAR DEFINITIVAMENTE') }}
                                </flux:button>
                            </flux:modal.trigger>
                        @else
                            <flux:modal.trigger name="confirm-delete-client">
                                <flux:button size="sm" variant="ghost" wire:click="confirmDelete('{{ $client->id }}')">
                                    {{ __('Eliminar') }}
                                </flux:button>
                            </flux:modal.trigger>
                        @endif
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                    {{ __('No hay clientes cargados.') }}
                </td>
            </tr>
        @endforelse

        {{-- Modales --}}
        <x-slot name="modal">
            {{-- Modal: Eliminar definitivamente --}}
            <flux:modal name="confirm-delete-client-force" class="min-w-[22rem]" x-data
                @client-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-client-force' })">
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

            {{-- Modal: Eliminar (marcar como deleted) --}}
            <flux:modal name="confirm-delete-client" class="min-w-[22rem]" x-data
                @client-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-client' })">
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
        </x-slot>
    </x-data-table>
</section>
