<footer role="contentinfo" aria-label="Pie de página"
        class="mt-auto py-8 text-center
               bg-indigo-700 text-white
               dark:bg-indigo-600">

  <div class="container mx-auto px-6">
    <div class="mb-2 text-indigo-50 dark:text-indigo-100">
      &copy; {{ date('Y') }} LNQ-Core. Herramienta profesional para entrenadores.
    </div>

    <div class="space-x-2">
      <a href="{{route('central.contact')}}"
         class="underline underline-offset-4 decoration-white/60 hover:decoration-white transition
                text-white hover:text-indigo-100 focus:outline-none focus:ring-2 focus:ring-white/70 rounded-sm" wire:navigate>
        Contacto
      </a>
      <span aria-hidden="true">·</span>
      <a href="{{ route('central.terms') }}" wire:navigate
         class="underline underline-offset-4 decoration-white/60 hover:decoration-white transition
                text-white hover:text-indigo-100 focus:outline-none focus:ring-2 focus:ring-white/70 rounded-sm"  wire:navigate>
        Términos y condiciones
      </a>
    </div>
  </div>
</footer>
