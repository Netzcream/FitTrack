<header
    class="sticky top-0 z-20 px-6 sm:px-8 py-4
         bg-white/85 backdrop-blur
         border-b border-gray-200
         shadow flex items-center justify-between">
    <a href="{{ route('home') }}"
        class="flex items-center gap-2 text-2xl font-extrabold tracking-wide
            text-indigo-700"
        wire:navigate>
        <x-application-logo class="h-9 w-9" />
        FitTrack
    </a>

    <nav class="flex items-center gap-3 md:gap-6">
        <a href="{{ route('home') }}#funciona"
            class="font-medium text-slate-900 hover:text-indigo-700">
            Cómo funciona
        </a>

        <a href="{{ route('home') }}#beneficios"
            class="font-medium text-slate-900 hover:text-indigo-700">
            Beneficios
        </a>

        <a href="{{ route('home') }}#precios"
            class="font-medium text-slate-900 hover:text-indigo-700">
            Precios
        </a>

        <a href="{{ route('home') }}#faq"
            class="font-medium text-slate-900 hover:text-indigo-700">
            FAQ
        </a>

        <a href="{{ route('central.contact') }}"
            class="ml-2 px-4 py-2 rounded-md font-semibold shadow transition
              bg-indigo-600 hover:bg-indigo-700 text-white"
            wire:navigate>
            Probar gratis
        </a>
        @auth
            <a href="{{ route('dashboard') }}"
               class="font-medium text-slate-900 hover:text-indigo-700">
                Gestión
            </a>
        @endauth
    </nav>
</header>

