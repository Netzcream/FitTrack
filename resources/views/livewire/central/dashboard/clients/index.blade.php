    <div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 space-y-6">
        {{-- Header --}}
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('central.clients') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">{{ __('central.clients_subtitle') }}</flux:subheading>
                </div>

                <flux:button
                    as="a"
                    href="{{ route('central.dashboard.clients.create') }}"
                    variant="primary"
                    icon="plus"
                    wire:navigate
                >
                    {{ __('site.new') }}
                </flux:button>
            </div>

            <flux:separator variant="subtle" />
        </div>

        {{-- Tabla de clientes --}}
        <div class="mt-5 w-full">
            @include('livewire.central.dashboard.clients.partials.clients-list')
        </div>
    </div>
</div>



