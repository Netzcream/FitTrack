    <div class="flex items-start max-md:flex-col">
        <div class="flex-1 self-stretch max-md:pt-6">

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

            <div class="mt-5 w-full ">

                <section class="w-full">


                    @include('livewire.central.dashboard.clients.partials.clients-list')


                </section>
            </div>
        </div>
    </div>



