<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6">
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('site.commercial_plans') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">
                        {{ __('site.commercial_plans_subheading') }}
                    </flux:subheading>
                </div>
                <flux:button as="a" href="{{ route('tenant.dashboard.commercial-plans.create') }}"
                    variant="primary" icon="plus">
                    {{ __('site.new_plan') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        </div>

        <section class="w-full">
            <x-data-table :pagination="$plans">
                <x-slot name="filters">
                    <div class="flex flex-wrap gap-4 w-full items-end">
                        <div class="max-w-[260px] flex-1">
                            <flux:label class="text-xs">{{ __('site.search') }}</flux:label>
                            <flux:input size="sm" wire:model.live.debounce.400ms="search"
                                placeholder="{{ __('site.search_placeholder') }}" class="w-full" />
                        </div>

                        <div class="min-w-[150px]">
                            <flux:label class="text-xs">{{ __('site.visibility') }}</flux:label>
                            <flux:select size="sm" wire:model="visibility">
                                <option value="">{{ __('site.all') }}</option>
                                <option value="public">{{ __('site.public') }}</option>
                                <option value="private">{{ __('site.private') }}</option>
                            </flux:select>
                        </div>

                        <div class="min-w-[150px]">
                            <flux:label class="text-xs">{{ __('site.type') }}</flux:label>
                            <flux:select size="sm" wire:model="planType">
                                <option value="">{{ __('site.all') }}</option>
                                @foreach (['free', 'standard', 'pro', 'enterprise'] as $t)
                                    <option value="{{ $t }}">{{ __('site.plan_type_' . $t) }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="min-w-[150px]">
                            <flux:label class="text-xs">{{ __('site.billing') }}</flux:label>
                            <flux:select size="sm" wire:model="billingInterval">
                                <option value="">{{ __('site.all') }}</option>
                                @foreach (['monthly', 'yearly', 'both'] as $b)
                                    <option value="{{ $b }}">{{ __('site.billing_' . $b) }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="min-w-[150px]">
                            <flux:label class="text-xs">{{ __('site.active') }}</flux:label>
                            <flux:select size="sm" wire:model="active">
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
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer  text-left"
                        wire:click="sort('name')">
                        <span class="inline-flex items-center gap-1">{{ __('site.name') }}
                            @if ($sortBy === 'name')
                                {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                            @endif
                        </span>
                    </th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 text-left">
                        {{ __('site.code') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer  text-left"
                        wire:click="sort('monthly_price')">
                        {{ __('site.monthly') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer text-left"
                        wire:click="sort('yearly_price')">
                        {{ __('site.yearly') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 text-left">
                        {{ __('site.billing') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 text-left">
                        {{ __('site.active') }}
                    </th>
                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase dark:text-neutral-500 cursor-pointer text-left"
                        wire:click="sort('sort_order')">
                        {{ __('site.order') }}
                    </th>
                    <th class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                        {{ __('site.actions') }}
                    </th>
                </x-slot>

                @forelse ($plans as $plan)
                    <tr>
                        <td class="align-top px-6 py-4 text-sm font-medium text-gray-800 dark:text-neutral-200">
                            {{ $plan->name }}
                            <div class="text-xs text-gray-500">{{ $plan->slug }}</div>
                        </td>
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $plan->code }}
                        </td>
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $plan->monthly_price !== null ? number_format($plan->monthly_price, 2) . ' ' . $plan->currency : '—' }}
                        </td>
                        <td class="align-top px-6 py-4 text-sm text-gray-800 dark:text-neutral-200">
                            {{ $plan->yearly_price !== null ? number_format($plan->yearly_price, 2) . ' ' . $plan->currency : '—' }}
                        </td>
                        <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                            {{ __('site.billing_' . $plan->billing_interval) }}
                        </td>
                        <td class="align-top px-6 py-4 text-sm">
                            <span
                                class="text-xs {{ $plan->is_active ? 'text-green-600 dark:text-green-400' : 'text-gray-500' }}">
                                {{ $plan->is_active ? __('site.yes') : __('site.no') }}
                            </span>
                        </td>
                        <td class="align-top px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                            {{ $plan->sort_order }}
                        </td>
                        <td class="align-top px-6 py-4 text-end text-sm font-medium">
                            <span
                                class="text-xs text-gray-400 dark:text-neutral-500 inline-flex items-center whitespace-nowrap">
                                <flux:button wire:navigate size="sm"
                                    href="{{ route('tenant.dashboard.commercial-plans.edit', $plan) }}">
                                    {{ __('site.edit') }}
                                </flux:button>
                                <flux:modal.trigger name="confirm-delete-plan">
                                    <flux:button size="sm" variant="ghost" type="button"
                                        wire:click="confirmDelete({{ $plan->id }})">
                                        {{ __('site.delete') }}
                                    </flux:button>
                                </flux:modal.trigger>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-neutral-400">
                            {{ __('site.no_plans_found') }}
                        </td>
                    </tr>
                @endforelse

                <x-slot name="modal">
                    <flux:modal name="confirm-delete-plan" class="min-w-[22rem]" x-data
                        @plan-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-plan' })">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('site.delete_plan_title') }}</flux:heading>
                                <flux:text class="mt-2">
                                    {{ __('site.delete_plan_message') }}
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
