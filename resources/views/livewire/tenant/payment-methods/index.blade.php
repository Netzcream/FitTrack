<div class="flex items-start max-md:flex-col">
  <div class="flex-1 self-stretch max-md:pt-6">
    <div class="relative mb-6 w-full">
      <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
          <flux:heading size="xl" level="1">{{ __('site.payment_methods') }}</flux:heading>
          <flux:subheading size="lg" class="mb-6">
            {{ __('site.payment_methods_subheading') }}
          </flux:subheading>
        </div>
        <flux:button as="a" href="{{ route('tenant.dashboard.payment-methods.create') }}"
          variant="primary" icon="plus">
          {{ __('site.new_payment_method') }}
        </flux:button>
      </div>
      <flux:separator variant="subtle" />
    </div>

    <section class="w-full">
      <x-data-table :pagination="$methods">
        <x-slot name="filters">
          <x-index-filters :searchPlaceholder="__('site.search_placeholder')">
            <x-slot name="additionalFilters">
              {{-- Filtro por estado activo --}}
              <div class="min-w-[150px]">
                <flux:select size="sm" wire:model.live="status" :label="__('site.active')">
                  <option value="">{{ __('site.all') }}</option>
                  <option value="1">{{ __('site.yes') }}</option>
                  <option value="0">{{ __('site.no') }}</option>
                </flux:select>
              </div>
            </x-slot>
          </x-index-filters>
        </x-slot>

        <x-slot name="head">
          <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer text-left"
              wire:click="sort('name')">
            <span class="inline-flex items-center gap-1">{{ __('site.name') }}
              @if ($sortBy === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
            </span>
          </th>
          <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer text-left"
              wire:click="sort('code')">
            <span class="inline-flex items-center gap-1">{{ __('site.code') }}
              @if ($sortBy === 'code') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
            </span>
          </th>
          <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 text-left">
            {{ __('site.active') }}
          </th>
          <th class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
            {{ __('site.actions') }}
          </th>
        </x-slot>

        @forelse ($methods as $method)
          <tr wire:key="payment-method-{{ $method->id }}">
            <td class="align-top px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
              {{ $method->name }}
            </td>
            <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
              {{ $method->code }}
            </td>
            <td class="align-top px-6 py-4 text-sm">
              @php
                $state = $method->is_active ? 'active' : 'inactive';
                $styles = [
                  'active' => 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/40 dark:text-green-300 dark:ring-green-900',
                  'inactive' => 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800',
                ];
              @endphp
              <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $styles[$state] }}">
                {{ __('common.' . $state) }}
              </span>
            </td>
            <td class="align-top px-6 py-4 text-end text-sm font-medium">
              <span class="text-xs text-gray-400 dark:text-neutral-500 inline-flex items-center whitespace-nowrap">
                <flux:button wire:navigate size="sm"
                  href="{{ route('tenant.dashboard.payment-methods.edit', $method) }}">
                  {{ __('site.edit') }}
                </flux:button>
                <flux:modal.trigger name="confirm-delete-payment-method">
                  <flux:button size="sm" variant="ghost" type="button"
                    wire:click="confirmDelete({{ $method->id }})">
                    {{ __('site.delete') }}
                  </flux:button>
                </flux:modal.trigger>
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
              {{ __('site.no_payment_methods_found') }}
            </td>
          </tr>
        @endforelse

        <x-slot name="modal">
          <flux:modal name="confirm-delete-payment-method" class="min-w-[22rem]" x-data
            @payment-method-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-payment-method' })">
            <div class="space-y-6">
              <div>
                <flux:heading size="lg">{{ __('site.delete_payment_method_title') }}</flux:heading>
                <flux:text class="mt-2">
                  {{ __('site.delete_payment_method_message') }}
                </flux:text>
              </div>
              <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                  <flux:button variant="ghost">{{ __('site.cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">
                  {{ __('site.confirm_delete') }}
                </flux:button>
              </div>
            </div>
          </flux:modal>
        </x-slot>
      </x-data-table>
    </section>
  </div>
</div>
