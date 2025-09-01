<x-data-table :pagination="$contacts">
    <x-slot name="filters">
        <div class="max-w-[210px] flex-1">
            <flux:input wire:keyup.debounce.500ms="filter()" wire:model.debounce.500ms="search"
                label="{{ __('Buscar') }}" />
        </div>

    </x-slot>

    <x-slot name="head">
        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer"
            wire:click="sort('name')">
            <span class="inline-flex items-center gap-1 whitespace-nowrap">
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
            wire:click="sort('phone')">
            {{ __('Teléfono') }}
        </th>
        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer"
            wire:click="sort('created_at')">
            {{ __('Fecha') }}
        </th>
        <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
            {{ __('Mensaje') }}
        </th>
        <th class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
            {{ __('Acciones') }}
        </th>

    </x-slot>

    @forelse ($contacts as $contact)
        <tr>
            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
                {{ $contact->name }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                {{ $contact->email }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                {{ $contact->mobile }}
            </td>

            <td class="px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                {{ $contact->created_at->format('d/m/Y H:i') }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200 max-w-[250px] truncate"
                title="{{ $contact->message }}">
                {{ Illuminate\Support\Str::limit($contact->message, 70) }}
            </td>
            <td class="px-6 py-4 text-end text-sm font-medium">
                <span class="inline-flex items-center gap-x-2 text-xs text-gray-400 dark:text-neutral-500">
                    <a wire:navigate href="{{ route('tenant.dashboard.contacts.show', $contact->uuid) }}"
                        class="text-blue-600 hover:text-blue-800 dark:text-blue-500 dark:hover:text-blue-400">
                        {{ __('Ver') }}
                    </a>

                    <flux:modal.trigger name="confirm-delete-contact">
                        <button wire:click="confirmDelete('{{ $contact->uuid }}')"
                            class="ml-2 text-red-600 hover:text-red-800 dark:text-red-500 dark:hover:text-red-400">
                            {{ __('Eliminar') }}
                        </button>
                    </flux:modal.trigger>
                </span>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                {{ __('No hay contactos recibidos.') }}
            </td>
        </tr>
    @endforelse

    <x-slot name="modal">
        <flux:modal name="confirm-delete-contact" class="min-w-[22rem]" x-data
            @contact-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-contact' })">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('¿Eliminar contacto?') }}</flux:heading>
                    <flux:text class="mt-2">
                        {{ __('Esta acción eliminará el contacto. ¿Estás seguro?') }}
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
