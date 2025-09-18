<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="xl" level="1">
                    {{ $editMode ? __('site.edit_commercial_plan') : __('site.new_commercial_plan') }}
                </flux:heading>
                <flux:subheading size="lg" class="mb-6">
                    {{ $editMode ? __('site.update_pricing_limits_availability') : __('site.define_pricing_limits_availability') }}
                </flux:subheading>
                <flux:separator variant="subtle" />
            </div>
            <div class="max-w-5xl space-y-6">
                {{-- Basics --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input wire:model.defer="name" label="{{ __('site.name') }}" required autocomplete="off" />
                    <flux:input wire:model.defer="code" label="{{ __('site.code') }}" required autocomplete="off" />
                    <flux:input wire:model.defer="slug" label="{{ __('site.slug_optional') }}" />
                    <div class="md:col-span-2">
                        <flux:textarea wire:model.defer="description" label="{{ __('site.description') }}"
                            rows="3" />
                    </div>
                </div>
                @error('name')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
                @error('code')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror

                {{-- Pricing --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <flux:input type="number" step="0.01" wire:model.defer="monthly_price"
                        label="{{ __('site.monthly_price') }}" />
                    <flux:input type="number" step="0.01" wire:model.defer="yearly_price"
                        label="{{ __('site.yearly_price') }}" />
                    <flux:input wire:model.defer="currency" label="{{ __('site.currency') }}" maxlength="3" />
                    <div>
                        <flux:label>{{ __('site.billing_interval') }}</flux:label>
                        <flux:select wire:model.defer="billing_interval">
                            @foreach (['monthly', 'yearly', 'both'] as $b)
                                <option value="{{ $b }}">{{ __('site.billing_' . $b) }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                {{-- Order & visibility --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input type="number" wire:model.defer="sort_order" label="{{ __('site.sort_order') }}"
                        min="0" />
                    <div>
                        <flux:label>{{ __('site.visibility') }}</flux:label>
                        <flux:select wire:model.defer="visibility">
                            <option value="public">{{ __('site.public') }}</option>
                            <option value="private">{{ __('site.private') }}</option>
                        </flux:select>
                    </div>
                </div>

                {{-- ===== Features (collection: key/value) ===== --}}
                <div class="space-y-3">
                    <flux:label>{{ __('site.features') }}</flux:label>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                        <flux:input size="sm" wire:model.defer="featureKey"
                            placeholder="{{ __('site.key_placeholder_support') }}" />
                        <flux:input size="sm" wire:model.defer="featureValue"
                            placeholder="{{ __('site.value_placeholder_example') }}" />
                        <flux:button type="button" wire:click="addFeature" variant="ghost" size="sm"
                            class="justify-self-start">
                            {{ __('site.add') }}
                        </flux:button>
                    </div>

                    <div class="flex flex-col">
                        <div class="-m-1.5 overflow-x-auto">
                            <div class="p-1.5 min-w-full inline-block align-middle">
                                <div class="overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                        <thead>
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                                    {{ __('site.key') }}
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                                    {{ __('site.value') }}
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                                    {{ __('site.action') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                            @forelse ($featuresList as $idx => $f)
                                                <tr>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200">
                                                        {{ $f['key'] }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                                        {{ $f['value'] }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                                        <flux:button size="xs" variant="ghost" type="button"
                                                            wire:click="removeFeature({{ $idx }})">
                                                            {{ __('site.delete') }}
                                                        </flux:button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3"
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-neutral-400">
                                                        {{ __('site.no_features_yet') }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== Additional limits (collection: key/int value) ===== --}}
                <div class="space-y-3">
                    <flux:label>{{ __('site.additional_limits') }}</flux:label>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                        <flux:input size="sm" wire:model.defer="limitKey"
                            placeholder="{{ __('site.key_placeholder_exports') }}" />
                        <flux:input size="sm" type="number" min="0" wire:model.defer="limitValue"
                            placeholder="{{ __('site.value_placeholder_number') }}" />
                        <flux:button type="button" wire:click="addLimit" variant="ghost" size="sm"
                            class="justify-self-start">
                            {{ __('site.add') }}
                        </flux:button>
                    </div>

                    <div class="flex flex-col">
                        <div class="-m-1.5 overflow-x-auto">
                            <div class="p-1.5 min-w-full inline-block align-middle">
                                <div class="overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                        <thead>
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                                    {{ __('site.key') }}
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                                    {{ __('site.value') }}
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                                                    {{ __('site.action') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                            @forelse ($limitsList as $idx => $l)
                                                <tr>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-neutral-200">
                                                        {{ $l['key'] }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                                                        {{ $l['value'] }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                                                        <flux:button size="xs" variant="ghost" type="button"
                                                            wire:click="removeLimit({{ $idx }})">
                                                            {{ __('site.delete') }}
                                                        </flux:button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3"
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-neutral-400">
                                                        {{ __('site.no_limits_yet') }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Active --}}
                <div class="flex items-center gap-3">
                    <flux:checkbox wire:model.defer="is_active" />
                    <flux:label>{{ __('site.active') }}</flux:label>
                </div>

                <div class="flex justify-end gap-4 pt-6 items-center">
                    <x-tenant.action-message on="updated">
                        {{ __('site.saved') }}
                    </x-tenant.action-message>
                    <flux:checkbox label="{{ __('site.back_list') }}" wire:model="back" />

                    <flux:button as="a" variant="ghost"
                        href="{{ route('tenant.dashboard.commercial-plans.index') }}">
                        {{ $editMode ? __('site.back') : __('site.cancel') }}
                    </flux:button>

                    <flux:button type="submit" variant="primary">
                        {{ $editMode ? __('site.update_plan') : __('site.create_plan') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
