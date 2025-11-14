<x-layouts.app :title="__('site.dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">


          @include('partials.dashboard-top-cards')



        {{-- Quick Actions --}}
        <section class="w-full">
            <div
                class="rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Acciones rápidas') }}</h2>
                <div class="flex flex-wrap gap-4">
                    <flux:button as="a" href="{{ route('central.dashboard.clients.create') }}" icon="plus"
                        wire:navigate>
                        {{ __('Nuevo Entrenador') }}
                    </flux:button>

                    <flux:button as="a" href="{{ route('central.dashboard.settings') }}" icon="cog"
                        wire:navigate>
                        {{ __('Configuración') }}
                    </flux:button>
                </div>
            </div>
        </section>


        @livewire('central.dashboard.jobs-table')

    </div>
</x-layouts.app>
