<x-layouts.app :title="__('site.dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        {{-- Top Cards --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            {{-- Tenants --}}
            <a href="{{ route('central.dashboard.clients.index') }}" wire:navigate
                class="relative aspect-video flex flex-col items-center justify-center bg-neutral-100 dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-700 hover:shadow-lg transition-shadow">

                <svg xmlns="http://www.w3.org/2000/svg" class="size-10 mb-2" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-layout-dashboard-icon lucide-layout-dashboard">
                    <rect width="7" height="9" x="3" y="3" rx="1" />
                    <rect width="7" height="5" x="14" y="3" rx="1" />
                    <rect width="7" height="9" x="14" y="12" rx="1" />
                    <rect width="7" height="5" x="3" y="16" rx="1" />
                </svg>


                <div class="text-lg font-semibold">{{ __('Clientes') }}</div>
                <div class="text-xs text-neutral-500 mt-1">
{{ \App\Models\Tenant::where('status', \App\Enums\TenantStatus::ACTIVE)->count() }} {{ __('activos') }}
                </div>
            </a>

            {{-- Usuarios --}}
            <a href="#" wire:navigate
                class="relative aspect-video flex flex-col items-center justify-center bg-neutral-100 dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-700 hover:shadow-lg transition-shadow">


                <svg xmlns="http://www.w3.org/2000/svg" class="size-10 mb-2" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-users-icon lucide-users">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <path d="M16 3.128a4 4 0 0 1 0 7.744" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                    <circle cx="9" cy="7" r="4" />
                </svg>

                <div class="text-lg font-semibold">{{ __('Usuarios') }}</div>
                <div class="text-xs text-neutral-500 mt-1">
                    {{ \App\Models\User::count() }} {{ __('registrados') }}
                </div>
            </a>

            {{-- Logs (si tenés visor activo) --}}
            <a href="{{ route('central.dashboard.log-viewer') }}" wire:navigate
                class="relative aspect-video flex flex-col items-center justify-center bg-neutral-100 dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-700 hover:shadow-lg transition-shadow">


                <svg xmlns="http://www.w3.org/2000/svg" class="size-10 mb-2" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-terminal-icon lucide-terminal">
                    <path d="M12 19h8" />
                    <path d="m4 17 6-6-6-6" />
                </svg>

                <div class="text-lg font-semibold">{{ __('Registros') }}</div>
                <div class="text-xs text-neutral-500 mt-1">
                    {{ __('Logs recientes') }}
                </div>
            </a>
        </div>

        {{-- Quick Actions --}}
        <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-8">
            <div class="flex flex-wrap gap-4">
                <flux:button as="a" href="{{ route('central.dashboard.clients.create') }}" icon="plus"
                    wire:navigate>
                    {{ __('Nuevo Cliente') }}
                </flux:button>

                <flux:button as="a" href="{{ route('central.dashboard.settings') }}" icon="cog"
                    wire:navigate>
                    {{ __('Configuración') }}
                </flux:button>
            </div>
        </div>
    </div>
</x-layouts.app>
