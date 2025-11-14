<div class="flex items-start max-md:flex-col">
  <div class="flex-1 self-stretch max-md:pt-6 space-y-6">

    {{-- Header --}}
    <div class="relative mb-6 w-full">
      <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
          <flux:heading size="xl" level="1">{{ __('central.contacts.index_title') }}</flux:heading>
          <flux:subheading size="lg" class="mb-6">
            @if ($noLeidos > 0)
              {!! trans_choice('central.contacts.index_subheading_unread', $noLeidos, ['count' => $noLeidos]) !!}
            @else
              {{ __('central.contacts.index_subheading') }}
            @endif
          </flux:subheading>
        </div>
      </div>
      <flux:separator variant="subtle" />
    </div>

    {{-- Tabla principal --}}
    <section class="w-full">
      <x-data-table :pagination="$contacts">

        {{-- Filtros --}}
        <x-slot name="filters">
          <div class="flex flex-wrap gap-4 w-full items-end">
            <div class="max-w-[260px] flex-1">
              <flux:input
                size="sm"
                class="w-full"
                wire:model.live.debounce.250ms="q"
                :label="__('common.search')"
                placeholder="{{ __('central.contacts.search_placeholder') }}" />
            </div>

            <div>
              <flux:button size="sm" variant="ghost" wire:click="$set('q', '')">
                {{ __('common.clear') }}
              </flux:button>
            </div>

            @if (!empty($selected))
              <div class="ms-auto flex gap-2 flex-wrap items-center">
                <span class="text-sm text-gray-600 dark:text-neutral-300">
                  {{ trans_choice('central.contacts.selected_count', count($selected), ['count' => count($selected)]) }}
                </span>

                <flux:button size="sm" wire:click="markSelectedAsRead">
                  {{ __('central.contacts.mark_as_read') }}
                </flux:button>

                <flux:button size="sm"  wire:click="markSelectedAsUnread">
                  {{ __('central.contacts.mark_as_unread') }}
                </flux:button>

                <flux:button size="sm"  wire:click="clearSelection">
                  {{ __('common.clear_selection') }}
                </flux:button>

                <flux:modal.trigger name="confirm-delete-contacts">
                  <flux:button size="sm" variant="ghost">
                    {{ __('central.contacts.delete_selected') }}
                  </flux:button>
                </flux:modal.trigger>
              </div>
            @endif
          </div>
        </x-slot>

        {{-- Encabezado --}}
        <x-slot name="head">
          <th class="px-4 py-3 text-start">
            <input type="checkbox" class="rounded border-gray-300" wire:model.live="selectPage">
          </th>
          <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
            {{ __('central.contacts.name') }}
          </th>
          <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
            {{ __('central.contacts.email') }}
          </th>
          <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
            {{ __('central.contacts.phone') }}
          </th>
          <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
            {{ __('central.contacts.status') }}
          </th>
          <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
            {{ __('common.actions') }}
          </th>
        </x-slot>

        {{-- Filas --}}
        @forelse ($contacts as $contact)
          <tr wire:key="contact-{{ $contact->id }}" @class(['bg-yellow-50 dark:bg-neutral-800' => $contact->unread])>
            <td class="px-4 py-4">
              <input type="checkbox" class="rounded border-gray-300" value="{{ $contact->id }}" wire:model.live="selected">
            </td>
            <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">{{ $contact->name }}</td>
            <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
              @if($contact->email)
                <a href="mailto:{{ $contact->email }}" class="hover:underline">{{ $contact->email }}</a>
              @endif
            </td>
            <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
              {{ $contact->phone ?? '-' }}
            </td>
            <td class="px-6 py-4 text-sm">
              <button type="button" wire:click="toggleRead('{{ $contact->id }}')"
                class="p-2 rounded hover:bg-gray-100 dark:hover:bg-neutral-700 transition">
                @if ($contact->unread)
                  <x-icons.lucide.mail class="w-5 h-5 text-gray-700 dark:text-neutral-200" />
                @else
                  <x-icons.lucide.mail-open class="w-5 h-5 text-gray-400 dark:text-neutral-500" />
                @endif
              </button>
            </td>
            <td class="px-6 py-4 text-end text-sm font-medium space-x-2">
              <flux:modal.trigger name="view-contact">
                <flux:button size="sm" wire:click="openAndMark('{{ $contact->id }}')">
                  {{ __('common.view') }}
                </flux:button>
              </flux:modal.trigger>
              <flux:modal.trigger name="confirm-delete-contact">
                <flux:button variant="ghost" size="sm" wire:click="confirmDeleteAsk('{{ $contact->id }}')">
                  {{ __('common.delete') }}
                </flux:button>
              </flux:modal.trigger>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="100" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
              {{ __('common.empty_state') }}
            </td>
          </tr>
        @endforelse

        {{-- Modales --}}
        <x-slot name="modal">

          {{-- Ver contacto --}}
          <flux:modal name="view-contact" class="w-2xl" x-data>
            <div class="space-y-6">
              @if ($viewing)
                <flux:heading size="lg">{{ __('central.contacts.contact_detail') }}</flux:heading>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                  <div><strong>{{ __('central.contacts.name') }}:</strong> {{ $viewing->name }}</div>
                  <div><strong>{{ __('central.contacts.email') }}:</strong> {{ $viewing->email }}</div>
                  <div><strong>{{ __('central.contacts.phone') }}:</strong> {{ $viewing->phone ?? '-' }}</div>
                </div>
                <div class="text-sm text-gray-700 dark:text-neutral-300">
                  <p class="mt-4 whitespace-normal break-words">{{ $viewing->message }}</p>
                </div>
              @else
                <flux:text>{{ __('central.contacts.no_contact_selected') }}</flux:text>
              @endif
              <div class="flex justify-end gap-2">
                <flux:modal.close>
                  <flux:button variant="ghost">{{ __('common.close') }}</flux:button>
                </flux:modal.close>
              </div>
            </div>
          </flux:modal>

          {{-- Confirmar eliminación individual --}}
          <flux:modal name="confirm-delete-contact" class="min-w-[22rem]" x-data
            @contact-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-contact' })">
            <div class="space-y-6">
              <flux:heading size="lg">{{ __('central.contacts.confirm_delete_contact_title') }}</flux:heading>
              <flux:text class="mt-2">{{ __('central.contacts.confirm_delete_contact_text') }}</flux:text>
              <div class="flex gap-2 justify-end">
                <flux:modal.close>
                  <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="deleteConfirmed">
                  {{ __('common.delete') }}
                </flux:button>
              </div>
            </div>
          </flux:modal>

          {{-- Confirmar eliminación múltiple --}}
          <flux:modal name="confirm-delete-contacts" class="min-w-[22rem]" x-data
            @contacts-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-contacts' })">
            <div class="space-y-6">
              <flux:heading size="lg">{{ __('central.contacts.confirm_delete_selected_title') }}</flux:heading>
              <flux:text class="mt-2">
                {{ trans_choice('central.contacts.confirm_delete_selected_text', count($selected), ['count' => count($selected)]) }}
              </flux:text>
              <div class="flex gap-2 justify-end">
                <flux:modal.close>
                  <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="deleteSelectedConfirmed">
                  {{ __('central.contacts.delete_selected') }}
                </flux:button>
              </div>
            </div>
          </flux:modal>

        </x-slot>
      </x-data-table>
    </section>
  </div>
</div>
