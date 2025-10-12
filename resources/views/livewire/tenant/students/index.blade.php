<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">

        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('students.index_title') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">{{ __('students.index_subheading') }}</flux:subheading>
                </div>

                <flux:button as="a" href="{{ route('tenant.dashboard.students.create') }}" variant="primary"
                    icon="plus">
                    {{ __('students.new_student') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        <section class="w-full">
            <x-data-table :pagination="$students">

                <x-slot name="filters">
                    <div class="flex flex-wrap gap-4 w-full items-end">
                        <div class="max-w-[260px] flex-1">
                            <flux:input size="sm" class="w-full" wire:model.live.debounce.250ms="q"
                                :label="__('common.search')" placeholder="{{ __('students.search_placeholder') }}" />
                        </div>

                        <div class="min-w-[160px]">

                            <flux:select size="sm" wire:model.live="status" :label="__('students.status')">
                                <option value="">{{ __('common.all') }}</option>
                                <option value="active">{{ __('students.status.active') }}</option>
                                <option value="paused">{{ __('students.status.paused') }}</option>
                                <option value="inactive">{{ __('students.status.inactive') }}</option>
                                <option value="prospect">{{ __('students.status.prospect') }}</option>
                            </flux:select>
                        </div>

                        <div class="min-w-[160px]">

                            <flux:select size="sm" wire:model.live="plan" :label="__('students.plan')">
                                <option value="">{{ __('common.all') }}</option>
                                @foreach ($plans as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="">
                            <flux:button size="sm" variant="ghost" wire:click="resetFilters">
                                {{ __('common.clear') }}
                            </flux:button>
                        </div>
                    </div>
                </x-slot>

                <x-slot name="head">
                    <th wire:click="sort('last_name')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        <span class="inline-flex items-center gap-1">
                            {{ __('students.name') }}
                            @if ($sortBy === 'last_name')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('students.status') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('students.plan') }}
                    </th>

                    <th wire:click="sort('last_login_at')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        {{ __('students.last_login_at') }}
                    </th>

                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                        {{ __('common.actions') }}
                    </th>
                </x-slot>

                @forelse ($students as $student)
                    <tr wire:key="student-{{ $student->uuid }}"
                        class="divide-y divide-gray-200 dark:divide-neutral-700">
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            <div class="inline-flex items-center gap-3">
                                <div
                                    class="h-8 w-8 rounded-full overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center">
                                    @if ($student->hasMedia('avatar'))
                                        <img src="{{ $student->getFirstMediaUrl('avatar', 'thumb') }}"
                                            alt="{{ $student->full_name }}" class="object-cover h-full w-full">
                                    @else
                                        <span
                                            class="text-xs font-semibold">{{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="leading-tight">
                                    <div class="font-medium text-gray-900 dark:text-neutral-100">
                                        {{ $student->full_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">{{ $student->email }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="align-top px-6 py-4 text-sm">
                            @php
                                $state = $student->status;
                                $styles = [
                                    'active' =>
                                        'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/40 dark:text-green-300 dark:ring-green-900',
                                    'paused' =>
                                        'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-900',
                                    'inactive' =>
                                        'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800',
                                    'prospect' =>
                                        'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-900',
                                ];
                            @endphp
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $styles[$state] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800' }}">
                                {{ __('students.status.' . $state) }}
                            </span>
                        </td>

                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $student->commercialPlan?->name ?? '—' }}
                        </td>

                        <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                            {{ $student->last_login_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>

                        <td class="align-top px-6 py-4 text-end text-sm font-medium">
                            <span
                                class="inline-flex items-center gap-2 space-x-1 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">
                                <flux:button size="sm" as="a" wire:navigate
                                    href="{{ route('tenant.dashboard.students.edit', $student->uuid) }}">
                                    {{ __('common.edit') }}
                                </flux:button>

                                <flux:modal.trigger name="confirm-delete-student">
                                    <flux:button size="sm" variant="ghost"
                                        wire:click="confirmDelete('{{ $student->uuid }}')">
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


                <x-slot name="modal">
                    <flux:modal name="confirm-delete-student" class="min-w-[22rem]" x-data
                        @student-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-student' })">
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
