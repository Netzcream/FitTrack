<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">{{ __('site.contacts') }}
            <br>

            @if ($noLeidos > 0)
                <p class="text-sm font-normal text-gray-400">
                    {!! trans_choice('site.contacts_not_read', $noLeidos, ['count' => $noLeidos]) !!}</p>
            @endif

        </h2>

    </div>

    {{-- Mensajes flash --}}


    {{-- Toolbar de acciones bulk (visible cuando hay selección) --}}
    @if (!empty($selected))
        <div class="mb-4 flex items-center gap-2 flex-wrap">
            <span class="text-sm text-gray-600 dark:text-neutral-300">
                {{ trans_choice('site.selected_count', count($selected), ['count' => count($selected)]) }}
            </span>

            {{-- Botones usando TU componente --}}
            <x-button type="button" color="neutral" wire:click="markSelectedAsRead">
                {{ __('site.mark_as_read') }}
            </x-button>

            <x-button type="button" color="secondary" wire:click="markSelectedAsUnread">
                {{ __('site.mark_as_unread') }}
            </x-button>

            <x-button type="button" color="neutral" wire:click="clearSelection">
                {{ __('site.clear_selection') }}
            </x-button>
            <flux:modal.trigger name="confirm-delete-contacts">
                <x-button type="button" color="danger" wire:click="confirmBulkDeleteAsk">
                    {{ __('site.delete_selected') }}
                </x-button>
            </flux:modal.trigger>

        </div>

        {{-- Aviso de “seleccionaste la página actual” --}}
        @if ($selectPage && !$selectAll)
            <div class="mb-3 rounded-md bg-amber-50 border border-amber-200 px-3 py-2 text-sm text-amber-800">
                {{ __('site.selected_page_hint') }}
                <button type="button" class="underline" wire:click="selectAll">
                    {{ __('site.select_all_results') }}
                </button>
            </div>
        @endif
    @endif

    <div class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead>
                            <tr>
                                {{-- Checkbox header: seleccionar página actual --}}
                                <th class="px-4 py-3 text-start">
                                    <input type="checkbox" class="rounded border-gray-300" wire:model.live="selectPage">
                                </th>

                                <th
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    {{ __('site.name') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    {{ __('site.email') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    {{ __('site.phone') }}
                                </th>

                                <th
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                    {{ __('site.status') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 w-0 text-end">
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @foreach ($contacts as $contact)
                                <tr @class(['bg-yellow-50 dark:bg-neutral-800' => $contact->unread]) wire:key="row-{{ $contact->id }}">
                                    {{-- Checkbox fila --}}
                                    <td class="px-4 py-4">
                                        <input type="checkbox" class="rounded border-gray-300"
                                            value="{{ $contact->id }}" wire:model.live="selected">
                                    </td>

                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200">
                                        {{ $contact->name }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                        @if(!empty($contact->email))
                                        <a href="mailto:{{$contact->email}}" class="hover:underline">
                                        {{ $contact->email }}
                                        </a>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                        {{ $contact->phone ?? '-' }}
                                    </td>



                                    {{-- Ícono de sobre (toggle read/unread) --}}
                                    <td
                                        class="px-6 py-4  whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">

                                        <button type="button"
                                            class="p-2 rounded hover:bg-gray-100 dark:hover:bg-neutral-700 transition"
                                            title="{{ $contact->unread ? __('site.mark_as_read') : __('site.mark_as_unread') }}"
                                            wire:click="toggleRead('{{ $contact->id }}')">
                                            @if ($contact->unread)
                                                <x-icons.lucide.mail
                                                    class="w-5 h-5 text-gray-700 dark:text-neutral-200" />
                                            @else
                                                <x-icons.lucide.mail-open
                                                    class="w-5 h-5 text-gray-400 dark:text-neutral-500" />
                                            @endif
                                            <span class="sr-only">
                                                {{ $contact->unread ? __('site.unread') : __('site.read') }}
                                            </span>
                                        </button>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                        <flux:modal.trigger name="view-contact">

                                            <flux:button wire:click="openAndMark('{{ $contact->id }}')"
                                                size="sm">
                                                {{ __('site.view') }}</flux:button>


                                        </flux:modal.trigger>

                                        <flux:modal.trigger name="confirm-delete-contact">

                                            <flux:button variant="ghost"
                                                wire:click="confirmDeleteAsk('{{ $contact->id }}')" size="sm">
                                                {{ __('site.delete') }}</flux:button>

                                        </flux:modal.trigger>

                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $contacts->links('components.preline.pagination') }}
                    </div>
                </div>
            </div>
        </div>
        {{--
        @if (session('message'))
            <div class="mb-3 rounded-md bg-emerald-50 border border-emerald-200 px-3 py-2 text-sm text-emerald-800">
                {{ session('message') }}
            </div>
        @endif
        --}}
    </div>

    <flux:modal name="view-contact" class="w-2xl" x-data>
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">
                        {{ __('site.contact_detail') }}
                    </flux:heading>
                    @if ($viewing)
                        <flux:text class="mt-1 text-sm">
                            {{ __('site.received_at') }}:
                            {{ $viewing->created_at?->format('d/m/Y H:i') }}
                        </flux:text>
                    @endif
                </div>


            </div>

            @if ($viewing)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500 dark:text-neutral-400">{{ __('site.name') }}</div>
                        <div class="font-medium text-gray-900 dark:text-neutral-100">{{ $viewing->name }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-neutral-400">{{ __('site.email') }}</div>
                        <div class="font-medium text-gray-900 dark:text-neutral-100">{{ $viewing->email }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-neutral-400">{{ __('site.phone') }}</div>
                        <div class="font-medium text-gray-900 dark:text-neutral-100">{{ $viewing->phone ?? '-' }}</div>
                    </div>



                    @if (!empty($viewing->config))
                        <div class="sm:col-span-2">
                            <div class="text-gray-500 dark:text-neutral-400">{{ __('site.extra_config') }}</div>
                            <pre class="mt-1 p-2 rounded bg-gray-50 dark:bg-neutral-900 overflow-auto text-xs">
{!! json_encode($viewing->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
                        </pre>
                        </div>
                    @endif
                </div>

                <div class="space-y-2">
                    <div class="text-gray-500 dark:text-neutral-400 text-sm">{{ __('site.message') }}</div>
                    <div class=" whitespace-normal break-words text-gray-900 dark:text-neutral-100">
                        {{ $viewing->message }}
                    </div>
                </div>
            @else
                <flux:text>{{ __('site.no_contact_selected') }}</flux:text>
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <x-button type="button" color="neutral">{{ __('site.close') }}</x-button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="confirm-delete-contact" class="min-w-[22rem]" x-data
        @contact-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-contact' })">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('site.confirm_delete_contact_title') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('site.confirm_delete_contact_text') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <x-button type="button" color="neutral">{{ __('site.cancel') }}</x-button>
                </flux:modal.close>

                <x-button type="button" color="danger" wire:click="deleteConfirmed">
                    {{ __('site.delete') }}
                </x-button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="confirm-delete-contacts" class="min-w-[22rem]" x-data
        @contacts-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-contacts' })">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('site.confirm_delete_selected_title') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ trans_choice('site.confirm_delete_selected_text', count($selected), ['count' => count($selected)]) }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <x-button type="button" color="neutral">{{ __('site.cancel') }}</x-button>
                </flux:modal.close>

                <x-button type="button" color="danger" wire:click="deleteSelectedConfirmed">
                    {{ __('site.delete_selected') }}
                </x-button>
            </div>
        </div>
    </flux:modal>


</div>
