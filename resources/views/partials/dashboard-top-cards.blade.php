<section class="w-full">
    <div class="space-y-4">
        <div class="grid gap-4 md:grid-cols-3">


            <a href="{{ route('central.dashboard.clients.index') }}" wire:navigate
                class="relative rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden hover:shadow-lg transition-shadow
                  flex flex-col justify-end text-white h-30">
                <div class="absolute inset-0 bg-center bg-cover"
                    style="background-image: url('/images/cards/clients.webp')"></div>
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute inset-0 bg-black/25"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-black/10 to-transparent">
                    </div>
                </div>
                <div class="relative z-10 p-4">
                    <div class="flex items-center gap-2">
                        <x-icons.lucide.layout-dashboard class="size-5" />
                        <span class="text-lg font-semibold">{{ __('Clientes') }}</span>
                    </div>
                    <div class="text-xs opacity-90 mt-1">
                        {{ \App\Models\Tenant::where('status', \App\Enums\TenantStatus::ACTIVE)->count() }}
                        {{ __('activos') }}
                    </div>
                </div>
            </a>

            {{-- Usuarios --}}
            <a href="#" wire:navigate
                class="relative rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden hover:shadow-lg transition-shadow
                  flex flex-col justify-end text-white h-30">
                <div class="absolute inset-0 bg-center bg-cover"
                    style="background-image: url('/images/cards/users.webp')"></div>
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute inset-0 bg-black/25"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-black/10 to-transparent">
                    </div>
                </div>
                <div class="relative z-10 p-4">
                    <div class="flex items-center gap-2">
                        <x-icons.lucide.users class="size-5" />
                        <span class="text-lg font-semibold">{{ __('Usuarios') }}</span>
                    </div>
                    <div class="text-xs opacity-90 mt-1">
                        {{ \App\Models\User::count() }} {{ __('registrados') }}
                    </div>
                </div>
            </a>

            {{-- Logs --}}
            <a href="{{ route('central.dashboard.log-viewer') }}" wire:navigate
                class="relative rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden hover:shadow-lg transition-shadow
                  flex flex-col justify-end text-white h-30">
                <div class="absolute inset-0 bg-center bg-cover"
                    style="background-image: url('/images/cards/logs.webp')"></div>
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute inset-0 bg-black/25"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-black/10 to-transparent">
                    </div>
                </div>
                <div class="relative z-10 p-4">
                    <div class="flex items-center gap-2">
                        <x-icons.lucide.terminal class="size-5" />
                        <span class="text-lg font-semibold">{{ __('Registros') }}</span>
                    </div>
                    <div class="text-xs opacity-90 mt-1">{{ __('Logs recientes') }}</div>
                </div>
            </a>

        </div>
    </div>
</section>
