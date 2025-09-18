<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="xl" level="1">
                    {{ $editMode ? __('site.edit_training_phase') : __('site.new_training_phase') }}
                </flux:heading>
                <flux:subheading size="lg" class="mb-6">
                    {{ __('site.training_phase_subheading') }}
                </flux:subheading>
                <flux:separator variant="subtle" />
            </div>

            <div class="max-w-5xl space-y-6">
                {{-- Basics --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input wire:model.defer="name" label="{{ __('site.name') }}" required autocomplete="off" />
                    <flux:input wire:model.defer="code" label="{{ __('site.code') }}" required autocomplete="off" />
                </div>
                @error('name')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
                @error('code')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror

                {{-- Active --}}
                <div class="flex items-center gap-3">
                    <flux:checkbox wire:model.defer="is_active" />
                    <flux:label>{{ __('site.active') }}</flux:label>
                </div>

                <div class="flex justify-end gap-4 pt-6 items-center">
                    <x-tenant.action-message on="updated">
                        {{ __('site.saved') }}
                    </x-tenant.action-message>

                    <flux:checkbox label="{{ __('site.back_list') }}" wire:model.live="back" />

                    <flux:button as="a" variant="ghost"
                        href="{{ route('tenant.dashboard.training-phases.index') }}">
                        {{ $editMode ? __('site.back') : __('site.cancel') }}
                    </flux:button>

                    <flux:button type="submit" variant="primary">
                        {{ $editMode ? __('site.update_phase') : __('site.create_phase') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
