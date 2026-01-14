<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6 space-y-6">
        {{-- Header --}}
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('payments.index_title') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">{{ __('payments.index_subheading') }}</flux:subheading>
                </div>
                <flux:button as="a" href="{{ route('tenant.dashboard.billing.invoices.create') }}" variant="primary" icon="plus" wire:navigate>
                    {{ __('invoices.create_invoice') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        <x-data-table :pagination="$invoices">
            <x-slot name="filters">
                <div class="flex flex-nowrap gap-3 w-full items-end">
                    <div class="max-w-[260px] flex-1">
                        <flux:input size="sm" wire:model.live.debounce.250ms="search" :label="__('common.search')"
                            :placeholder="__('payments.search_placeholder')" />
                    </div>

                    <flux:select size="sm" wire:model.live="status" :label="__('common.status')" class="min-w-[140px]">
                        <option value="">{{ __('common.all') }}</option>
                        <option value="pending">{{ __('payments.status.pending') }}</option>
                        <option value="overdue">{{ __('payments.status.overdue') }}</option>
                        <option value="paid">{{ __('payments.status.paid') }}</option>
                        <option value="cancelled">{{ __('payments.status.cancelled') }}</option>
                    </flux:select>

                    <flux:input size="sm" type="date" wire:model.live="dueFrom" :label="__('payments.due_from')" class="min-w-[160px]" />
                    <flux:input size="sm" type="date" wire:model.live="dueTo" :label="__('payments.due_to')" class="min-w-[160px]" />
                    <flux:input size="sm" wire:model.live="paymentMethod" :label="__('payments.method')"
                        placeholder="{{ __('payments.method_placeholder') }}" class="min-w-[180px]" />

                    <flux:button size="sm" variant="ghost" wire:click="clearFilters">
                        {{ __('common.clear') }}
                    </flux:button>
                </div>
            </x-slot>

            <x-slot name="head">
                <th class="px-6 py-3 text-xs font-medium uppercase text-start text-gray-500 dark:text-neutral-500">
                    {{ __('payments.student') }}</th>
                <th class="px-6 py-3 text-xs font-medium uppercase text-start text-gray-500 dark:text-neutral-500">
                    {{ __('invoices.concept') }}</th>
                <th class="px-6 py-3 text-xs font-medium uppercase text-start text-gray-500 dark:text-neutral-500">
                    {{ __('payments.amount') }}</th>
                <th class="px-6 py-3 text-xs font-medium uppercase text-start text-gray-500 dark:text-neutral-500">
                    {{ __('payments.method') }}</th>
                <th class="px-6 py-3 text-xs font-medium uppercase text-start text-gray-500 dark:text-neutral-500">
                    {{ __('common.status') }}</th>
                <th class="px-6 py-3 text-xs font-medium uppercase text-start text-gray-500 dark:text-neutral-500">
                    {{ __('payments.due_date') }}</th>
                <th class="px-6 py-3 text-xs font-medium uppercase text-start text-gray-500 dark:text-neutral-500">
                    {{ __('payments.paid_at') }}</th>
                <th class="px-6 py-3 text-xs font-medium uppercase text-end text-gray-500 dark:text-neutral-500">
                    {{ __('common.actions') }}</th>
            </x-slot>

            @forelse ($invoices as $invoice)
                <tr wire:key="invoice-{{ $invoice->id }}" class="divide-y divide-gray-200 dark:divide-neutral-700">
                    <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                        <div class="leading-tight">
                            <div class="font-medium text-gray-900 dark:text-neutral-100">
                                {{ $invoice->student?->first_name }} {{ $invoice->student?->last_name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">{{ $invoice->student?->email ?? '--' }}</div>
                        </div>
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                        @php
                            $meta = $invoice->meta ?? [];
                            $label = $meta['label'] ?? null;
                            $planName = $meta['plan_name'] ?? null;
                            $notes = $meta['notes'] ?? null;
                            $concept = $label ?: ($planName ? 'Plan ' . $planName : __('invoices.manual_label'));
                        @endphp
                        <div class="font-medium text-gray-900 dark:text-neutral-100">{{ $concept }}</div>
                        @if ($notes)
                            <div class="text-xs text-gray-500 dark:text-neutral-400">{{ $notes }}</div>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                        {{ $invoice->formatted_amount }}</td>

                    <td class="px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                        {{ $invoice->payment_method ? strtoupper($invoice->payment_method) : '--' }}</td>

                    <td class="px-6 py-4 text-sm">
                        @php
                            $styles = [
                                'paid' =>
                                    'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-950/40 dark:text-green-300 dark:ring-green-900',
                                'pending' =>
                                    'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-900',
                                'overdue' =>
                                    'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900',
                                'cancelled' =>
                                    'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800',
                            ];
                        @endphp
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $styles[$invoice->status] ?? 'bg-gray-50 text-gray-700 ring-1 ring-gray-200 dark:bg-neutral-900/60 dark:text-neutral-300 dark:ring-neutral-800' }}">
                            {{ __('payments.status.' . $invoice->status) }}
                        </span>
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                        {{ optional($invoice->due_date)->format('d/m/Y') ?? '--' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                        {{ optional($invoice->paid_at)->format('d/m/Y H:i') ?? '--' }}</td>

                    <td class="px-6 py-4 text-sm text-end">
                        <div class="inline-flex gap-2">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button size="sm" variant="ghost" icon="ellipsis-vertical">
                                    {{ __('common.actions') }}
                                </flux:button>
                                <flux:menu class="w-48">
                                    <flux:menu.item as="a" href="{{ route('tenant.dashboard.students.edit', $invoice->student) }}"
                                        icon="user" wire:navigate>
                                        {{ __('payments.open_student') }}
                                    </flux:menu.item>

                                    @if ($invoice->is_pending)
                                        <flux:modal.trigger name="manual-payment">
                                            <flux:menu.item icon="check-circle" wire:click="prepareManualPayment({{ $invoice->id }})">
                                                {{ __('payments.register_payment') }}
                                            </flux:menu.item>
                                        </flux:modal.trigger>
                                        <flux:menu.item icon="clock" wire:click="markAsOverdue({{ $invoice->id }})">
                                            {{ __('payments.mark_as_overdue') }}
                                        </flux:menu.item>
                                        <flux:menu.item icon="x-mark" wire:click="cancelInvoice({{ $invoice->id }})">
                                            {{ __('payments.cancel_invoice') }}
                                        </flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-neutral-400">
                        {{ __('common.empty_state') }}
                    </td>
                </tr>
            @endforelse

            <x-slot name="modal">
                <flux:modal name="manual-payment" class="min-w-[24rem]" x-data
                    @invoice-marked.window="$dispatch('modal-close', { name: 'manual-payment' })">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">{{ __('payments.manual_payment_title') }}</flux:heading>
                            <flux:text class="mt-2">{{ __('payments.manual_payment_msg') }}</flux:text>
                        </div>

                        @if ($selectedInvoice)
                            <div class="rounded-lg border border-gray-200 dark:border-neutral-700 p-4 text-sm">
                                <div class="font-medium text-gray-900 dark:text-neutral-100">
                                    {{ $selectedInvoice->student?->full_name ?? '' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400">
                                    {{ __('payments.amount') }}: {{ $selectedInvoice->formatted_amount }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400">
                                    {{ __('payments.due_date') }}: {{ optional($selectedInvoice->due_date)->format('d/m/Y') ?? '--' }}
                                </div>
                            </div>
                        @endif

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <flux:input wire:model.defer="manualPaymentMethod" :label="__('payments.method')"
                                    placeholder="{{ __('payments.method_placeholder') }}" />
                                @if (!empty($acceptedMethods))
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">
                                        {{ __('payments.method_hint', ['methods' => strtoupper(implode(', ', $acceptedMethods))]) }}
                                    </div>
                                @endif
                            </div>

                            <flux:input wire:model.defer="manualPaidAt" :label="__('payments.paid_at')" type="datetime-local" />
                            <flux:input wire:model.defer="manualPaymentReference" :label="__('payments.payment_reference')" />
                            <flux:textarea wire:model.defer="manualPaymentNotes" :label="__('payments.payment_notes')" />
                        </div>

                        <div class="flex gap-2">
                            <flux:spacer />
                            <flux:modal.close>
                                <flux:button variant="ghost">{{ __('common.cancel') }}</flux:button>
                            </flux:modal.close>
                            <flux:button wire:click="recordManualPayment" variant="primary">
                                {{ __('common.confirm') }}
                            </flux:button>
                        </div>
                    </div>
                </flux:modal>
            </x-slot>
        </x-data-table>
    </div>
</div>

