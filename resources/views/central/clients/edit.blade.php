<x-layouts.app  :title="__('site.edit').' - '.__('central.clients')">


    <div class="flex items-start max-md:flex-col">
        <div class="flex-1 self-stretch max-md:pt-6">

            <div class="relative mb-6 w-full">

                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <flux:heading size="xl" level="1">{{ __('central.clients') }}</flux:heading>
                        <flux:subheading size="lg">{{ __('central.edit_client') }}</flux:subheading>
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

                    <form action="{{ route('central.dashboard.clients.update', ['client' => $entity->id]) }}"
                        method="POST" class="space-y-2">
                        @csrf
                        @method('PUT')

                        <div>
                            <flux:label for="name">{{ __('Nombre del entrenador') }}</flux:label>
                            <flux:input class="mt-2" id="name" name="name" type="text"
                                :value="old('name', $entity->name)" placeholder="Ingrese nombre" required
                                autofocus />
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <flux:label for="admin_email">{{ __('Mail del administrador') }}</flux:label>
                            <flux:input class="mt-2" id="admin_email" name="admin_email" type="text"
                                :value="old('admin_email', $entity->admin_email)" placeholder="Ingrese mail del administrador" required
                                autofocus />
                            @error('admin_email')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>



                        <div>
                            <flux:label for="id">{{ __('Subdominio') }}</flux:label>
                            <flux:input class="mt-2" id="id" name="id" type="text"
                                :value="old('id', $entity->id)" disabled readonly />
                            @error('id')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>



                        <div>
                            <flux:label for="status">{{ __('Estado') }}</flux:label>
                            <select name="status" id="status"
                                class="mt-2 p-5 w-full border rounded-lg block disabled:shadow-none dark:shadow-none appearance-none text-base sm:text-sm py-2 h-10 leading-[1.375rem] ps-3 pe-3 bg-white dark:bg-white/10 dark:disabled:bg-white/[7%] text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200 dark:border-white/10 dark:disabled:border-white/5">
                                @foreach (\App\Enums\TenantStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($entity->status === $status)>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary" icon="check">
                                {{ __('Actualizar') }}
                            </flux:button>
                        </div>
                    </form>



                </section>
            </div>
        </div>
    </div>


</x-layouts.app>
