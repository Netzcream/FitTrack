 <section class="flex-1 bg-gradient-to-b from-white to-gray-50 py-14 sm:py-16 lg:py-20">
        <div class="w-full max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-10 lg:gap-12 px-6 sm:px-8">
            <div class="flex-1 flex flex-col items-start justify-center max-w-xl">
                <span class="mb-3 text-xs font-bold tracking-wide shadow px-4 py-1 rounded-full
                              bg-indigo-100 text-indigo-700">
                    Plataforma para entrenadores
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4 leading-tight drop-shadow
                           text-slate-900">
                    Digitalizá y profesionalizá tu trabajo como Personal Trainer
                </h1>
                <p class="text-lg sm:text-xl mb-8 text-slate-700">
                    Gestioná todos tus alumnos, rutinas y pagos en un solo lugar. Ahorrá tiempo, ofrecé más valor y hacé crecer tu negocio.
                </p>
                <a href="#registro"
                   class="px-6 sm:px-7 py-3 rounded-xl text-lg font-bold shadow-lg transition
                          bg-indigo-600 hover:bg-indigo-700 text-white hover:scale-105 active:scale-95">
                    Quiero evolucionar mi negocio
                </a>
                <div class="mt-6 flex gap-2 items-center">
                    <img src="https://placehold.co/40x40/4f46e5/fff?text=AP" class="w-10 h-10 rounded-full border-2 border-indigo-100" alt="Testimonio 1" />
                    <img src="https://placehold.co/40x40/4f46e5/fff?text=SC" class="w-10 h-10 rounded-full border-2 border-indigo-100" alt="Testimonio 2" />
                    <span class="text-sm ml-2 text-slate-700">Entrenadores felices y en expansión</span>
                </div>
            </div>
            <div class="flex-1 flex items-center justify-end w-full">
                <div class="relative w-72 sm:w-80 md:w-96 h-[440px] rounded-3xl shadow-xl border-2 border-gray-100 overflow-hidden">
                    <img src="/images/hero1.webp" alt="Hero 1" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 opacity-100 hero-slide">
                    <img src="/images/hero2.webp" alt="Hero 2" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 opacity-0 hero-slide">
                    <img src="/images/hero3.webp" alt="Hero 3" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 opacity-0 hero-slide">
                </div>
            </div>
            <script>
                const slides = document.querySelectorAll('.hero-slide');
                let current = 0;
                setInterval(() => {
                    slides[current].classList.remove('opacity-100');
                    slides[current].classList.add('opacity-0');
                    current = (current + 1) % slides.length;
                    slides[current].classList.remove('opacity-0');
                    slides[current].classList.add('opacity-100');
                }, 4000);
            </script>
        </div>
    </section>

