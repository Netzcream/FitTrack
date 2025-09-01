<x-layouts.app :title="__('Nuevo - Clientes')">


    <div class="flex items-start max-md:flex-col">
        <div class="flex-1 self-stretch max-md:pt-6">

            <div class="relative mb-6 w-full">

                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <flux:heading size="xl" level="1">{{ __('Clientes') }}</flux:heading>
                        <flux:subheading size="lg">{{ __('Nuevo cliente') }}</flux:subheading>
                    </div>

                    <flux:button as="a" href="{{ route('central.dashboard.clients.index') }}" variant="outline"
                        icon="chevron-left">
                        {{ __('Volver al listado') }}
                    </flux:button>
                </div>

                <flux:separator variant="subtle" />

            </div>

            <div class="mt-5 w-full max-w-lg">

                <section class="w-full">

                    <form action="{{ route('central.dashboard.clients.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <flux:label for="name">{{ __('Nombre del cliente') }}</flux:label>
                            <flux:input class="mt-2" id="name" name="name" type="text" :value="old('name')"
                                placeholder="Ingrese nombre" required autofocus />
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <flux:label for="admin_email">{{ __('Mail del administrador') }}</flux:label>
                            <flux:input class="mt-2" id="admin_email" name="admin_email" type="text" :value="old('admin_email')"
                                placeholder="Ingrese mail del administrador" required autofocus />
                            @error('admin_email')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary" icon="check">
                                {{ __('Guardar') }}
                            </flux:button>
                        </div>
                    </form>



                </section>
            </div>
        </div>
    </div>


</x-layouts.app>
