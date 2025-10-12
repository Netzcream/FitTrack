<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">

        {{-- Header --}}
        <div class="relative w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('payments.index_title') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">{{ __('payments.index_subheading') }}</flux:subheading>
                </div>
                <flux:button as="a" href="{{ route('tenant.dashboard.payments.create') }}" variant="primary"
                    icon="plus">
                    {{ __('payments.new_entity') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        {{-- Filtros --}}
        <div class="flex flex-wrap gap-4 w-full items-end">
            <div class="max-w-[260px] flex-1">

                <flux:input size="sm" class="w-full" :label="__('common.search')"
                    wire:model.live.debounce.250ms="q" placeholder="{{ __('payments.search_placeholder') }}" />
            </div>

            <div class="min-w-[150px]">

                <flux:select size="sm" wire:model.live="status" :label="__('common.status')">
                    <option value="">{{ __('common.all') }}</option>
                    <option value="pending">{{ __('payments.status.pending') }}</option>
                    <option value="paid">{{ __('payments.status.paid') }}</option>
                    <option value="overdue">{{ __('payments.status.overdue') }}</option>
                </flux:select>
            </div>

            <div class="flex items-end gap-2 ml-auto">
                <flux:button size="sm" variant="ghost" wire:click="resetFilters">{{ __('common.clear') }}
                </flux:button>
            </div>
        </div>

        {{-- Tabla --}}
        <section class="w-full">
            <x-data-table :pagination="$payments">
                <x-slot name="head">
                    <th wire:click="sort('student_id')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        <span class="inline-flex items-center gap-1">{{ __('payments.student') }}
                            @if ($sortBy === 'student_id')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>
                    <th wire:click="sort('amount')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        <span class="inline-flex items-center gap-1">{{ __('payments.amount') }}
                            @if ($sortBy === 'amount')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('payments.method') }}</th>
                    <th wire:click="sort('status')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        <span class="inline-flex items-center gap-1">{{ __('common.status') }}
                            @if ($sortBy === 'status')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>
                    <th wire:click="sort('due_date')"
                        class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 cursor-pointer text-left">
                        <span class="inline-flex items-center gap-1">{{ __('payments.due_date') }}
                            @if ($sortBy === 'due_date')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-left">
                        {{ __('payments.paid_at') }}</th>
                    <th class="px-6 py-3 text-xs font-medium uppercase text-gray-500 dark:text-neutral-500 text-end">
                        {{ __('common.actions') }}</th>
                </x-slot>

                @forelse ($payments as $payment)
                    <tr class="divide-y divide-gray-200 dark:divide-neutral-700">
                        {{-- Columna alumno --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            <div class="inline-flex items-center gap-3">
                                <div
                                    class="h-8 w-8 rounded-full overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center">
                                    <span class="text-xs text-gray-600 dark:text-neutral-400 font-medium">
                                        {{ strtoupper(substr($payment->student?->first_name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="leading-tight">
                                    <div class="font-medium text-gray-900 dark:text-neutral-100">
                                        {{ $payment->student?->first_name }} {{ $payment->student?->last_name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                                        {{ $payment->student?->email ?? '—' }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Monto --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200 text-end">
                            ${{ number_format($payment->amount, 2, ',', '.') }}
                        </td>

                        {{-- Método --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $payment->paymentMethod?->name ?? '—' }}
                        </td>

                        {{-- Estado --}}
                        <td class="align-top px-6 py-4 text-sm">
                            @php
                                $styles = [
                                    'paid' =>
                                        'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/40 dark:text-green-300 dark:ring-green-900',
                                    'pending' =>
                                        'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-900',
                                    'overdue' =>
                                        'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900',
                                ];
                            @endphp
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $styles[$payment->status] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800' }}">
                                {{ __('payments.status.' . $payment->status) }}
                            </span>
                        </td>

                        {{-- Fechas --}}
                        <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                            {{ optional($payment->due_date)->format('d/m/Y') ?? '—' }}</td>
                        <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                            {{ optional($payment->paid_at)->format('d/m/Y') ?? '—' }}</td>

                        {{-- Acciones --}}
                        <td class="align-top px-6 py-4 text-end text-sm font-medium">
                            <span
                                class="inline-flex items-center gap-2 space-x-1 text-xs text-gray-400 dark:text-neutral-500 whitespace-nowrap">
                                <flux:button size="sm" as="a" wire:navigate
                                    href="{{ route('tenant.dashboard.payments.edit', $payment) }}">
                                    {{ __('common.edit') }}
                                </flux:button>

                                @if ($payment->status !== 'paid')
                                    <flux:modal.trigger name="confirm-mark-paid">
                                        <flux:button size="sm" variant="ghost"
                                            wire:click="confirmMarkAsPaid({{ $payment->id }})">
                                            {{ __('payments.mark_as_paid') }}
                                        </flux:button>
                                    </flux:modal.trigger>
                                @endif
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                            {{ __('common.empty_state') }}
                        </td>
                    </tr>
                @endforelse

                {{-- Modal --}}
                <x-slot name="modal">
                    <flux:modal name="confirm-mark-paid" class="min-w-[22rem]" x-data
                        @payment-marked.window="$dispatch('modal-close', { name: 'confirm-mark-paid' })">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('payments.confirm_mark_paid_title') }}
                                </flux:heading>
                                <flux:text class="mt-2">{{ __('payments.confirm_mark_paid_msg') }}</flux:text>
                            </div>
                            <div class="flex gap-2">
                                <flux:spacer />
                                <flux:modal.close>
                                    <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                                </flux:modal.close>
                                <flux:button wire:click="markAsPaid" variant="primary">
                                    {{ __('common.confirm') }}
                                </flux:button>
                            </div>
                        </div>
                    </flux:modal>
                </x-slot>
            </x-data-table>
        </section>
    </div>
</div>
