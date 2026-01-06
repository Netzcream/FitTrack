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
                    <x-index-filters :searchPlaceholder="__('users.search_placeholder')">
                        <x-slot name="additionalFilters">
                            {{-- Filtro por rol --}}
                            <div class="min-w-[180px]">
                                <flux:select wire:model.live="role" size="sm" class="w-full" :label="__('users.role')">
                                    <option value="">{{ __('common.all') }}</option>
                                    @foreach ($roles as $r)
                                        <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                                    @endforeach
                                </flux:select>
                            </div>
                        </x-slot>
                    </x-index-filters>
                </x-slot>

                <x-slot name="head">
                    <th wire:click="sort('name')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        <span class="inline-flex items-center gap-1">
                            {{ __('users.name') }}
                            @if ($sortBy === 'name')
                                {!! $sortDirection === 'asc' ? '&#9650;' : '&#9660;' !!}
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
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('users.student') }}
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
                    <tr wire:key="user-{{ $user->id }}"
                        class="divide-y divide-gray-200 dark:divide-neutral-700">
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
                            @php
                                $userRoles = $user->roles;
                                $visibleCount = 2;
                                $totalCount = $userRoles->count();
                                $remainingCount = $totalCount - $visibleCount;
                            @endphp

                            <div class="inline-flex items-center gap-1 flex-wrap">
                                @foreach ($userRoles->take($visibleCount) as $role)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-900">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach

                                @if ($remainingCount > 0)
                                    <div x-data="{ open: false }" class="relative inline-block">
                                        <span
                                            @mouseenter="open = true"
                                            @mouseleave="open = false"
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800 cursor-help"
                                            x-ref="trigger"
                                        >
                                            +{{ $remainingCount }}
                                        </span>

                                        <div
                                            x-show="open"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            @mouseenter="open = true"
                                            @mouseleave="open = false"
                                            class="fixed z-[9999] w-56 rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-lg p-3"
                                            style="display: none;"
                                            x-anchor.bottom-start="$refs.trigger"
                                        >
                                            <div class="text-xs font-semibold text-gray-700 dark:text-neutral-300 mb-2">
                                                {{ __('Todos los roles') }} ({{ $totalCount }})
                                            </div>
                                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                                @foreach ($userRoles as $role)
                                                    <div class="text-xs text-gray-600 dark:text-neutral-400 flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                        {{ ucfirst($role->name) }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- Student asignado --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            @if ($user->student)
                                <a class="text-blue-600 dark:text-blue-400 underline"
                                    href="{{ route('tenant.dashboard.students.edit', $user->student->uuid) }}">
                                    {{ $user->student->full_name ?: $user->student->email }}
                                </a>
                            @else
                                <span class="text-gray-500 dark:text-neutral-400">—</span>
                            @endif
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
                        <td colspan="6" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
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
