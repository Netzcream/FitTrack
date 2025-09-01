<x-central-layout>

    @include('central.partials.hero')
    @include('central.partials.beneficios')
    @include('central.partials.how')
    @include('central.partials.plans')




    @include('central.partials.testimonials')
    @include('central.partials.faq')




    <!-- CTA mobile -->
    <a href="#registro"
        class="fixed md:hidden bottom-5 left-1/2 -translate-x-1/2 px-6 py-3 rounded-full shadow-lg font-bold text-lg z-50
              bg-indigo-600 hover:bg-indigo-700 text-white dark:bg-indigo-500 dark:hover:bg-indigo-600
              transition animate-bounce">
        Probar gratis como entrenador
    </a>
</x-central-layout>
