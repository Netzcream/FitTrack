<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">
        {{-- Header --}}
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('manuals.index_title') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">{{ __('manuals.index_subheading') }}</flux:subheading>
                </div>

                <flux:button
                    as="a"
                    href="{{ route('central.dashboard.manuals.create') }}"
                    variant="primary"
                    icon="plus"
                    wire:navigate
                >
                    {{ __('manuals.new_manual') }}
                </flux:button>
            </div>

            <flux:separator variant="subtle" />
        </div>

        {{-- Tabla de manuales --}}
        <div class="mt-5 w-full">
            @include('livewire.central.dashboard.manuals.partials.manuals-list')
        </div>
    </div>
</div>
