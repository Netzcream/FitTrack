<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6">

            {{-- Header --}}
            <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
                <div class="flex items-center justify-between gap-4 max-w-3xl">
                    <div>
                        <flux:heading size="xl" level="1">
                            {{ $editMode ? __('commercial_plans.edit_title') : __('commercial_plans.new_title') }}
                        </flux:heading>
                        <flux:subheading size="lg" class="mb-6">
                            {{ $editMode ? __('commercial_plans.edit_subheading') : __('commercial_plans.new_subheading') }}
                        </flux:subheading>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-tenant.action-message on="saved">{{ __('site.saved') }}</x-tenant.action-message>
                        <flux:checkbox size="sm" label="{{ __('site.back_list') }}" wire:model.live="back" />
                        <flux:button as="a" variant="ghost"
                            href="{{ route('tenant.dashboard.commercial-plans.index') }}" size="sm">
                            {{ __('site.back') }}
                        </flux:button>
                        <flux:button type="submit" size="sm">
                            {{ $editMode ? __('common.update') : __('common.create') }}
                        </flux:button>
                    </div>
                </div>
                <flux:separator variant="subtle" class="mt-2" />
            </div>

            {{-- Contenido --}}
            <div class="max-w-3xl space-y-6 pt-2">

                {{-- Nombre + Estado --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input wire:model.defer="name" label="{{ __('commercial_plans.name') }}" required
                        autocomplete="off" />
                    <div class="flex items-center gap-2 mt-2 md:mt-6">
                        <flux:checkbox wire:model.defer="is_active" size="sm" label="{{ __('common.active') }}" />
                    </div>
                </div>

                {{-- Descripción --}}
                <flux:textarea wire:model.defer="description" rows="3"
                    label="{{ __('commercial_plans.description') }}"
                    placeholder="{{ __('commercial_plans.description_placeholder') }}" />

                {{-- Pricing dinámico --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:label>{{ __('commercial_plans.pricing') }}</flux:label>
                        <flux:button size="sm" variant="ghost" wire:click.prevent="addPrice" icon="plus">
                            {{ __('common.add') }}
                        </flux:button>
                    </div>

                    @foreach ($pricing as $i => $price)
                        <div
                            class="p-3 border border-gray-200 dark:border-neutral-700 rounded-lg space-y-3 bg-neutral-50/40 dark:bg-neutral-900/40">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Tipo y moneda --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <flux:select wire:model.defer="pricing.{{ $i }}.type"
                                        label="{{ __('commercial_plans.type') }}" class="w-full">
                                        @foreach ($this->pricingTypeOptions as $opt)
                                            <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                        @endforeach
                                    </flux:select>
                                    <flux:input wire:model.defer="pricing.{{ $i }}.currency"
                                        label="{{ __('commercial_plans.currency') }}" placeholder="ARS" />
                                </div>

                                {{-- Monto y etiqueta --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <flux:input wire:model.defer="pricing.{{ $i }}.amount"
                                        label="{{ __('commercial_plans.amount') }}" type="number" step="0.01" />
                                    <flux:input wire:model.defer="pricing.{{ $i }}.label"
                                        label="{{ __('commercial_plans.label') }}" placeholder="Ej: ARS 8.000 / mes" />
                                </div>
                            </div>

                            {{-- Acción eliminar --}}
                            <div class="flex justify-end">
                                <flux:button size="xs" variant="ghost"
                                    wire:click.prevent="removePrice({{ $i }})">
                                    {{ __('common.delete') }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Features --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <flux:label>{{ __('commercial_plans.features') }}</flux:label>
                        <flux:button size="sm" variant="ghost" wire:click.prevent="addFeature" icon="plus">
                            {{ __('common.add') }}
                        </flux:button>
                    </div>

                    @foreach ($features as $i => $feature)
                        <div class="flex items-center gap-3">
                            <flux:input wire:model.defer="features.{{ $i }}" class="flex-1"
                                placeholder="{{ __('commercial_plans.feature_placeholder') }}" />

                            <flux:button variant="ghost"
                                wire:click.prevent="removeFeature({{ $i }})">
                                {{ __('common.delete') }}
                            </flux:button>
                        </div>
                    @endforeach
                </div>

                {{-- Límites --}}
                <div class="space-y-3">
                    <flux:label>{{ __('commercial_plans.limits') }}</flux:label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:input wire:model.defer="limits.sessions_per_week"
                            label="{{ __('commercial_plans.sessions_per_week') }}" type="number" min="0" />
                        <flux:input wire:model.defer="limits.video_calls"
                            label="{{ __('commercial_plans.video_calls') }}" type="number" min="0" />
                        <div class="flex items-center gap-2 mt-2 md:mt-6">
                            <flux:checkbox wire:model.defer="limits.in_person"
                                label="{{ __('commercial_plans.in_person') }}" />
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="pt-6 max-w-3xl">
                    <div class="flex justify-end gap-3 items-center text-sm opacity-80">
                        <x-tenant.action-message on="saved">{{ __('site.saved') }}</x-tenant.action-message>
                        <flux:checkbox size="sm" label="{{ __('site.back_list') }}" wire:model.live="back" />
                        <flux:button as="a" variant="ghost"
                            href="{{ route('tenant.dashboard.commercial-plans.index') }}" size="sm">
                            {{ __('site.back') }}
                        </flux:button>
                        <flux:button type="submit" size="sm">
                            {{ $editMode ? __('common.update') : __('common.create') }}
                        </flux:button>
                    </div>
                </div>
            </div>

            <flux:separator variant="subtle" class="mt-8" />
        </form>
    </div>
</div>
