<!-- PRECIOS -->
<section id="precios" class="py-14 sm:py-16 bg-white dark:bg-slate-900">
    <div class="max-w-6xl mx-auto px-6 sm:px-8">
        <h2 class="text-3xl font-extrabold text-slate-900 dark:text-slate-100 text-center mb-10">Planes para
            profesionales</h2>
        <div class="grid md:grid-cols-3 gap-6 md:gap-8">
            <!-- Starter -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-8 flex flex-col items-center border border-gray-200 dark:border-slate-700">
                <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-3">Starter</h3>
                <div class="text-3xl font-extrabold text-indigo-700 dark:text-indigo-400 mb-3">$0 <span
                        class="text-base font-normal text-slate-700 dark:text-slate-300">/mes</span></div>
                <ul class="text-slate-700 dark:text-slate-500 mb-6 space-y-1">
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-indigo-600 inline-block"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-check-icon lucide-check">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                        Hasta 5 alumnos
                    </li>
                    <li> <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-indigo-600 inline-block"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-check-icon lucide-check">
                            <path d="M20 6 9 17l-5-5" />
                        </svg> Rutinas y mensajes</li>
                    <li> <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-indigo-600 inline-block"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-check-icon lucide-check">
                            <path d="M20 6 9 17l-5-5" />
                        </svg> Estadísticas básicas</li>
                </ul>
                <x-primary-outline-button as="a"  href="{{ route('central.contact', ['plan' => 'starter']) }}">Probar gratis</x-primary-outline-button>
            </div>
            <!-- Pro -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-8 flex flex-col items-center border-2 border-indigo-100 dark:border-indigo-700 scale-105">
                <h3 class="text-xl font-bold text-indigo-700 dark:text-indigo-400 mb-3">Pro</h3>
                <div class="text-3xl font-extrabold text-indigo-700 dark:text-indigo-400 mb-3">$14.900 <span
                        class="text-base font-normal text-slate-700 dark:text-slate-300">/mes</span></div>
                <ul class="text-slate-700 dark:text-slate-500 mb-6 space-y-1">
                    <li> <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-indigo-600 inline-block"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-check-icon lucide-check">
                            <path d="M20 6 9 17l-5-5" />
                        </svg> Alumnos ilimitados</li>
                    <li> <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-indigo-600 inline-block"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-check-icon lucide-check">
                            <path d="M20 6 9 17l-5-5" />
                        </svg> Rutinas avanzadas</li>
                    <li> <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-indigo-600 inline-block"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-check-icon lucide-check">
                            <path d="M20 6 9 17l-5-5" />
                        </svg> Exportar estadísticas</li>
                    <li> <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-indigo-600 inline-block"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-check-icon lucide-check">
                            <path d="M20 6 9 17l-5-5" />
                        </svg> Branding y personalización</li>
                </ul>
                <x-primary-button as="a" href="{{ route('central.contact', ['plan' => 'pro']) }}">Ser Pro</x-primary-button>
            </div>
            <!-- Equipo -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-8 flex flex-col items-center border border-gray-200 dark:border-slate-700">
                <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-3">Equipo</h3>
                <div class="text-3xl font-extrabold text-indigo-700 dark:text-indigo-400 mb-3">$24.900 <span
                        class="text-base font-normal text-slate-700 dark:text-slate-300">/mes</span></div>
                <ul class="text-slate-700 dark:text-slate-500 mb-6 space-y-1">
                    <li> <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-indigo-600 inline-block"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-check-icon lucide-check">
                            <path d="M20 6 9 17l-5-5" />
                        </svg> Para estudios/gyms</li>
                    <li> <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-indigo-600 inline-block"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-check-icon lucide-check">
                            <path d="M20 6 9 17l-5-5" />
                        </svg> Multi-entrenador</li>
                    <li> <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-indigo-600 inline-block"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-check-icon lucide-check">
                            <path d="M20 6 9 17l-5-5" />
                        </svg> Soporte prioritario</li>
                </ul>
                <x-primary-outline-button as="a"  href="{{ route('central.contact', ['plan' => 'equipo']) }}">Contactar</x-primary-outline-button>
            </div>
        </div>
    </div>
</section>
