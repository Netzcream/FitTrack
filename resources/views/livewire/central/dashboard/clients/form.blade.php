<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6 w-full">

        {{-- Header --}}
        <div class="relative mb-6 w-full">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <flux:heading size="xl" level="1">{{ __('central.clients') }}</flux:heading>
                    <flux:subheading size="lg" class="mb-6">
                        {{ __('central.clients_subtitle') }}
                    </flux:subheading>
                </div>

                <flux:button as="a" href="{{ route('central.dashboard.clients.index') }}" variant="outline"
                    icon="chevron-left">
                    {{ __('Volver al listado') }}
                </flux:button>
            </div>

            <flux:separator variant="subtle" />
        </div>

        {{-- Contenido en dos columnas iguales --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full">

            {{-- Columna IZQUIERDA: Formulario (sin max-w) --}}
            <section class="w-full">
                <form wire:submit.prevent="save" class="space-y-4">
                    @if ($this->client)
                        <div>
                            <flux:label for="name">{{ __('Nombre del entrenador') }}</flux:label>
                            <flux:input class="mt-2" id="name" wire:model.defer="name" type="text"
                                placeholder="Ingrese nombre" required maxlength="24" autofocus />
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <flux:label for="admin_email">{{ __('Mail del administrador') }}</flux:label>
                            <flux:input class="mt-2" id="admin_email" wire:model.defer="admin_email" type="email"
                                placeholder="Ingrese mail del administrador" readonly disabled maxlength="255" />
                            @error('admin_email')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <flux:label for="id">{{ __('Subdominio') }}</flux:label>
                            <flux:input class="mt-2" id="id"
                                value="{{ $this->client?->id }}.{{ env('APP_DOMAIN') }}" type="text" readonly
                                disabled />
                            @error('id')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        @include('livewire.central.dashboard.clients.partials.new')
                    @endif

                    <div>
                        <flux:label for="status">{{ __('Estado') }}</flux:label>
                        <select wire:model.defer="status" id="status"
                            class="mt-2 p-5 w-full rounded-lg block text-base sm:text-sm py-2 h-10 bg-white dark:bg-white/10 text-zinc-700 dark:text-zinc-300">
                            <option value="">{{ __('Seleccionar') }}</option>
                            @foreach (\App\Enums\TenantStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>


                    <div>
                        <flux:label for="commercial_plan_id">{{ __('Plan') }}</flux:label>
                        <select wire:model="commercial_plan_id" id="commercial_plan_id"
                            class="mt-2 p-5 w-full rounded-lg block text-base sm:text-sm py-2 h-10 bg-white dark:bg-white/10 text-zinc-700 dark:text-zinc-300">
                            <option value="">{{ __('Seleccionar') }}</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected($plan->id == $commercial_plan_id)>
                                    {{ $plan->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('commercial_plan_id')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    @include('livewire.central.dashboard.clients.partials.domains')

                    <div class="flex justify-end">
                        <div class="flex items-center max-md:pt-6 gap-4">
                            @if ($hasChanges)
                                <flux:modal.trigger name="confirm-leave">
                                    <flux:button icon="arrow-uturn-left" variant="filled">
                                        {{ __('Volver al listado') }}
                                    </flux:button>
                                </flux:modal.trigger>
                            @else
                                <flux:button as="a" icon="arrow-uturn-left"
                                    href="{{ route('central.dashboard.clients.index') }}" variant="filled"
                                    wire:navigate>
                                    {{ __('Volver al listado') }}
                                </flux:button>
                            @endif

                            <flux:button wire:click="save" class="cursor-pointer" icon="cloud-arrow-up">
                                {{ __('Guardar') }}
                            </flux:button>

                            <x-tenant.action-message on="saved">
                                {{ __('site.saved') }}
                            </x-tenant.action-message>
                        </div>
                    </div>
                </form>
            </section>

            {{-- Columna DERECHA: Tabla de usuarios del tenant --}}
            <aside class="w-full md:col-span-2">
                @if ($this->client)
                    @livewire(
                        'central.dashboard.clients.tenant-users-table',
                        [
                            'tenantId' => $this->client->id,
                        ],
                        key('tenant-users-' . $this->client->id)
                    )
                @else
                    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                        <flux:heading size="lg" level="2">{{ __('Usuarios del tenant') }}</flux:heading>
                        <p class="text-sm text-zinc-600 dark:text-zinc-300 mt-2">
                            {{ __('Seleccioná o creá un entrenador para ver sus usuarios.') }}
                        </p>
                    </div>
                @endif
            </aside>

        </div>
    </div>
</div>
