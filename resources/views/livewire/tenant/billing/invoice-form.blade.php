<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="xl" level="1">{{ __('invoices.new_title') }}</flux:heading>
                <flux:subheading size="lg" class="mb-6">{{ __('invoices.new_subheading') }}</flux:subheading>
                <flux:separator variant="subtle" />
            </div>

            <div class="max-w-4xl space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:select wire:model.live="student_id" :label="__('invoices.student')" required>
                            <option value="">{{ __('invoices.student_placeholder') }}</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }}
                                </option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:input wire:model.defer="amount" :label="__('invoices.amount')" type="number"
                            step="0.01" :disabled="$autoAmount" required />
                    </div>
                </div>

                <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-neutral-400">
                    <flux:checkbox wire:model.live="autoAmount" size="sm" />
                    <span>{{ __('invoices.use_plan_amount_hint') }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input wire:model.defer="due_date" :label="__('invoices.due_date')" type="date" />
                    <flux:input wire:model.defer="label" :label="__('invoices.concept')"
                        placeholder="{{ __('invoices.concept_placeholder') }}" />
                </div>

                <flux:textarea wire:model.defer="notes" :label="__('invoices.notes')"
                    placeholder="{{ __('invoices.notes_placeholder') }}" />

                <div class="flex justify-end gap-4 pt-6 items-center">
                    <x-tenant.action-message on="updated">{{ __('site.saved') }}</x-tenant.action-message>

                    <flux:checkbox :label="__('site.back_list')" wire:model.live="back" />
                    <flux:button as="a" variant="ghost"
                        href="{{ route('tenant.dashboard.billing.invoices.index') }}">{{ __('common.cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">{{ __('invoices.create_button') }}</flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
