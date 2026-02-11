<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">

        {{-- Header --}}
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('contacts.index_title') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">{{ __('contacts.index_subheading') }}</flux:subheading>
                </div>


            </div>
            <flux:separator variant="subtle" />
        </div>

        {{-- Tabla con filtros --}}
        <section class="w-full">
            <x-data-table :pagination="$contacts">
                {{-- Filtros --}}
                <x-slot name="filters">
                    <x-index-filters :searchPlaceholder="__('contacts.search_placeholder')" />
                </x-slot>

                {{-- Encabezado --}}
                <x-slot name="head">
                    <th wire:click="sort('name')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        <span class="inline-flex items-center gap-1">
                            {{ __('contacts.name') }}
                            @if ($sortBy === 'name')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>

                    <th wire:click="sort('email')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        <span class="inline-flex items-center gap-1">
                            {{ __('contacts.email') }}
                            @if ($sortBy === 'email')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('contacts.message') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('contacts.mobile') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                        {{ __('common.actions') }}
                    </th>
                </x-slot>

                {{-- Filas --}}
                @forelse ($contacts as $contact)
                    <tr wire:key="contact-{{ $contact->uuid }}"
                        class="divide-y divide-gray-200 dark:divide-neutral-700">
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $contact->name }}
                        </td>
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $contact->email }}
                        </td>

                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200 max-w-[250px] truncate"
                            title="{{ $contact->message }}">
                            {{ Illuminate\Support\Str::limit($contact->message, 70) }}
                        </td>


                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $contact->mobile ?? '-' }}
                        </td>
                        <td class="align-top px-6 py-4 text-end text-sm font-medium">
                            <span
                                class="inline-flex items-center gap-2 space-x-1 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">


                                <flux:button size="sm" as="a" wire:navigate
                                    href="{{ route('tenant.dashboard.contacts.show', $contact->uuid) }}">
                                    {{ __('Ver') }}
                                </flux:button>



                                <flux:modal.trigger name="confirm-delete-contact">
                                    <flux:button size="sm" variant="ghost"
                                        wire:click="confirmDelete('{{ $contact->uuid }}')">
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
                    <flux:modal name="confirm-delete-contact" class="min-w-[22rem]" x-data
                        @contact-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-contact' })">
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
