    <div class="flex items-start max-md:flex-col">
        <div class="flex-1 self-stretch max-md:pt-6">

            <div class="relative mb-6 w-full">

                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <flux:heading size="xl" level="1">{{ __('Contactos recibidos') }}</flux:heading>
                        <flux:subheading size="lg" class="mb-6">
                            {{ __('Consultas y mensajes enviados desde la web.') }}
                        </flux:subheading>

                    </div>


                </div>

                <flux:separator variant="subtle" />

            </div>

            <div class="mt-5 w-full ">

                <section class="w-full">
                    @include('livewire.tenant.contacts.index-list')
                </section>
            </div>
        </div>
    </div>
