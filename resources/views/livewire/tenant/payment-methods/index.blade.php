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
          <div class="flex flex-wrap gap-4 w-full items-end">
            <div class="max-w-[260px] flex-1">

              <flux:input size="sm" wire:model.live.debounce.400ms="search" :label=" __('site.search')"
                placeholder="{{ __('site.search_placeholder') }}" class="w-full" />
            </div>

            <div class="min-w-[150px]">

              <flux:select size="sm" wire:model="active" :label="__('site.active')">
                <option value="">{{ __('site.all') }}</option>
                <option value="yes">{{ __('site.yes') }}</option>
                <option value="no">{{ __('site.no') }}</option>
              </flux:select>
            </div>

            <flux:button size="sm" variant="ghost" wire:click="filter" class="self-end">
              {{ __('site.filter') }}
            </flux:button>
          </div>
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
          <tr>
            <td class="align-top px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
              {{ $method->name }}
            </td>
            <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
              {{ $method->code }}
            </td>
            <td class="align-top px-6 py-4 text-sm">
              <span class="text-xs {{ $method->is_active ? 'text-green-600 dark:text-green-400' : 'text-gray-500' }}">
                {{ $method->is_active ? __('site.yes') : __('site.no') }}
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
