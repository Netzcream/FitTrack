<section class="w-full" wire:poll.5s>
    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 flex-wrap mb-6">
        <div>
            <flux:heading size="xl" level="1">Mensajes de Soporte</flux:heading>
            <flux:subheading size="lg">Conversaciones con entrenadores</flux:subheading>
        </div>
    </div>
    <flux:separator variant="subtle" class="mb-6" />

    {{-- Data Table --}}
    <x-data-table :pagination="$conversations">
        {{-- Filtros con componente --}}
        <x-slot name="filters">
            <x-index-filters searchPlaceholder="Buscar por entrenador..." />
        </x-slot>

        {{-- Head --}}
        <x-slot name="head">
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-start">
                Entrenador
            </th>
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-start">
                Último mensaje
            </th>
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-start">
                Fecha
            </th>
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-center">
                No leídos
            </th>
            <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                Actions
            </th>
        </x-slot>

        {{-- Rows --}}
        @forelse($conversations as $conversation)
            @if($conversation->tenant)
            <tr wire:key="conversation-{{ $conversation->uuid }}">
                {{-- Identidad: avatar + nombre --}}
                <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                    <div class="inline-flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center">
                            <span class="text-xs font-semibold text-gray-600 dark:text-neutral-400">T</span>
                        </div>
                        <div class="leading-tight">
                            <div class="font-medium text-gray-900 dark:text-neutral-100">
                                {{ $conversation->tenant->name ?? 'Unknown' }}
                            </div>
                        </div>
                    </div>
                </td>

                {{-- Último mensaje --}}
                <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400 line-clamp-2">
                    @if($conversation->messages->count() > 0)
                        {{ $conversation->messages->first()->body }}
                    @else
                        <span class="text-gray-400 dark:text-neutral-500">Sin mensajes</span>
                    @endif
                </td>

                {{-- Fecha --}}
                <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                    @if($conversation->last_message_at)
                        {{ $conversation->last_message_at->diffForHumans() }}
                    @else
                        —
                    @endif
                </td>

                {{-- No leídos --}}
                <td class="align-top px-6 py-4 text-center">
                    @if($conversation->unread_count > 0)
                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                            {{ $conversation->unread_count > 99 ? '99+' : $conversation->unread_count }}
                        </span>
                    @else
                        <span class="text-gray-400 dark:text-neutral-500">—</span>
                    @endif
                </td>

                {{-- Acciones --}}
                <td class="align-top px-6 py-4 text-end text-sm font-medium">
                    <span class="inline-flex items-center gap-2">
                        <flux:button size="sm" as="a" wire:navigate href="{{ route('central.dashboard.support.show', $conversation) }}">
                            Ver
                        </flux:button>
                    </span>
                </td>
            </tr>
            @endif
        @empty
            <tr>
                <td colspan="5" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                    No hay conversaciones de soporte.
                </td>
            </tr>
        @endforelse

        {{-- Modal --}}
        <x-slot name="modal">
            {{-- No hay modal para este listado --}}
        </x-slot>
    </x-data-table>
</section>
