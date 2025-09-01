<header
  class="sticky top-0 z-20 px-6 sm:px-8 py-4
         bg-white/85 dark:bg-slate-900/75 backdrop-blur
         border-b border-gray-200 dark:border-slate-700
         shadow flex items-center justify-between">
  <a href="{{ route('home') }}"
     class="flex items-center gap-2 text-2xl font-extrabold tracking-wide
            text-indigo-700 dark:text-indigo-500"
     wire:navigate>
    <x-application-logo class="h-9 w-9" />
    FitTrack
  </a>

  <nav class="flex items-center gap-3 md:gap-6">
    <a href="{{ route('home') }}#funciona"
       class="font-medium text-slate-900 hover:text-indigo-700
              dark:text-slate-200 dark:hover:text-indigo-400">
      Cómo funciona
    </a>

    <a href="{{ route('home') }}#beneficios"
       class="font-medium text-slate-900 hover:text-indigo-700
              dark:text-slate-200 dark:hover:text-indigo-400">
      Beneficios
    </a>

    <a href="{{ route('home') }}#precios"
       class="font-medium text-slate-900 hover:text-indigo-700
              dark:text-slate-200 dark:hover:text-indigo-400">
      Precios
    </a>

    <a href="{{ route('home') }}#faq"
       class="font-medium text-slate-900 hover:text-indigo-700
              dark:text-slate-200 dark:hover:text-indigo-400">
      FAQ
    </a>

    <a href="{{ route('register') }}"
       class="ml-2 px-4 py-2 rounded-md font-semibold shadow transition
              bg-indigo-600 hover:bg-indigo-700 text-white
              dark:bg-indigo-500 dark:hover:bg-indigo-600" wire:navigate>
      Probar gratis
    </a>

    <button type="button" id="themeToggle" aria-label="Cambiar tema" data-theme-toggle
            class="ml-2 inline-flex items-center gap-2 text-sm px-3 py-2 rounded-md transition
                   border border-gray-200 bg-white text-slate-900 hover:bg-gray-100
                   dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 pointer-events-none" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M12 18.25a6.25 6.25 0 1 0 0-12.5 6.25 6.25 0 0 0 0 12.5zM12 1.75a.75.75 0 0 1 .75.75v1a.75.75 0 0 1-1.5 0v-1a.75.75 0 0 1 .75-.75zm0 18.75a.75.75 0 0 1 .75.75v1a.75.75 0 0 1-1.5 0v-1a.75.75 0 0 1 .75-.75zM22.25 12a.75.75 0 0 1-.75.75h-1a.75.75 0 0 1 0-1.5h1a.75.75 0 0 1 .75.75zM4.5 12a.75.75 0 0 1-.75.75h-1a.75.75 0 0 1 0-1.5h1A.75.75 0 0 1 4.5 12zm14.4 6.15a.75.75 0 0 1 0 1.06l-.7.7a.75.75 0 1 1-1.06-1.06l.7-.7a.75.75 0 0 1 1.06 0zM6.86 4.35a.75.75 0 0 1 0 1.06l-.7.7A.75.75 0 1 1 5.1 5.05l.7-.7a.75.75 0 0 1 1.06 0zm10.6-0a.75.75 0 0 1 1.06 0l.7.7a.75.75 0 0 1-1.06 1.06l-.7-.7a.75.75 0 0 1 0-1.06zM6.86 18.15a.75.75 0 0 1 0 1.06l-.7.7a.75.75 0 1 1-1.06-1.06l.7-.7a.75.75 0 0 1 1.06 0z"/>
      </svg>
      <span class="hidden md:inline">Tema</span>
    </button>
  </nav>
</header>
