<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">

        {{-- Header --}}
        <div class="relative w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('users.index_title') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">
                        {{ __('users.index_subheading') }}
                    </flux:subheading>
                </div>
                <flux:button as="a" href="{{ route('tenant.dashboard.users.create') }}" variant="primary"
                    icon="plus">
                    {{ __('users.new_entity') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>



        {{-- Tabla --}}
        <section class="w-full">
            <x-data-table :pagination="$users">




                <x-slot name="filters">
                    <div class="flex flex-wrap gap-4 w-full items-end">
                        <div class="max-w-[260px] flex-1">

                            <flux:input wire:model.live.debounce.250ms="search" size="sm" class="w-full" :label="__('common.search')"
                                placeholder="{{ __('users.search_placeholder') }}" />
                        </div>

                        <div class="min-w-[180px]">

                            <flux:select wire:model.live="role" size="sm" class="w-full" :label="__('users.role')">
                                <option value="">{{ __('common.all') }}</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                                @endforeach
                            </flux:select>
                        </div>
                        <div>
                            <flux:button size="sm" variant="ghost" wire:click="resetFilters">
                                {{ __('common.clear') }}</flux:button>
                        </div>
                    </div>
                </x-slot>



                <x-slot name="head">
                    <th wire:click="sort('name')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        <span class="inline-flex items-center gap-1">{{ __('users.name') }}
                            @if ($sortBy === 'name')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>
                    <th wire:click="sort('email')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        {{ __('users.email') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('users.role') }}
                    </th>
                    <th wire:click="sort('created_at')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        {{ __('users.created_at') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                        {{ __('common.actions') }}
                    </th>
                </x-slot>

                @forelse ($users as $user)
                    <tr class="divide-y divide-gray-200 dark:divide-neutral-700">
                        {{-- Nombre --}}
                        <td class="align-top px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
                            {{ $user->name }}
                        </td>

                        {{-- Email --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $user->email }}
                        </td>

                        {{-- Roles --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            @foreach ($user->roles as $role)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-900 mr-1">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </td>

                        {{-- Alta --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                            {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '—' }}
                        </td>

                        {{-- Acciones --}}
                        <td class="align-top px-6 py-4 text-end text-sm font-medium">
                            <span
                                class="inline-flex items-center gap-2 space-x-1 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">
                                <flux:button size="sm" as="a" wire:navigate
                                    href="{{ route('tenant.dashboard.users.edit', $user->id) }}">
                                    {{ __('common.edit') }}
                                </flux:button>

                                @if (auth()->user()->id !== $user->id)
                                    <flux:modal.trigger name="confirm-delete-user">
                                        <flux:button size="sm" variant="ghost"
                                            wire:click="confirmDelete('{{ $user->id }}')">
                                            {{ __('common.delete') }}
                                        </flux:button>
                                    </flux:modal.trigger>
                                @endif
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                            {{ __('common.empty_state') }}
                        </td>
                    </tr>
                @endforelse

                <x-slot name="modal">
                    <flux:modal name="confirm-delete-user" class="min-w-[22rem]" x-data
                        @user-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-user' })">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('users.confirm_delete_title') }}</flux:heading>
                                <flux:text class="mt-2">{{ __('users.confirm_delete_msg') }}</flux:text>
                            </div>
                            <div class="flex gap-2">
                                <flux:spacer />
                                <flux:modal.close>
                                    <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                                </flux:modal.close>
                                <flux:button wire:click="delete" variant="danger">
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
