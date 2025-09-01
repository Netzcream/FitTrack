@if (tenant_config('landing_cards_show'))
    @php
        $cards = \App\Models\LandingCard::where('active', true)->orderBy('order')->get();
    @endphp
    @if (count($cards))

        <div class="w-full py-6">

            @if (tenant_config('landing_cards_title'))
                <div class="text-center">
                    <span class="text-2xl font-bold" style="color: {{ tenant_config('color_base', '#263d83') }};">
                        {!! tenant_config('landing_cards_title') !!}
                    </span>
                </div>
            @endif
            @if (tenant_config('landing_cards_subtitle'))
                <div class="text-center mb-6">
                    <span class="text-sm text-gray-600">
                        {!! tenant_config('landing_cards_subtitle') !!}
                    </span>
                </div>
            @endif

            <div class="max-w-6xl mx-auto text-center">


                <div class="swiper landingcard-swiper">
                    <div class="swiper-wrapper">
                        @foreach ($cards as $card)
                            <div class="swiper-slide pt-12 flex">
                                <a href="{{ $card->link }}" target="{{ $card->target }}"
                                    class="relative flex flex-col items-center justify-start text-center bg-white rounded-xl px-6 pb-6 h-full transition hover:scale-[1.02] shadow">

                                    {{-- Círculo flotante --}}
                                    <div class="absolute -top-10 w-20 h-20 rounded-full flex items-center justify-center shadow"
                                        style="background-color: {{ tenant_config('color_base', '#263d83') }};">
                                        @if ($card->getFirstMediaUrl('cover'))
                                            <img src="{{ $card->getFirstMediaUrl('cover') }}" alt=""
                                                class="w-full h-full object-contain rounded-full" />
                                        @endif
                                    </div>

                                    {{-- Título --}}
                                    <h3 class="text-sm font-bold mb-1 mt-4 pt-10"
                                        style="color: {{ tenant_config('color_base', '#263d83') }};">
                                        {!! $card->title !!}
                                    </h3>

                                    {{-- Descripción --}}
                                    @if ($card->text)
                                        <p class="text-xs text-gray-600 leading-snug">
                                            {!! $card->text !!}
                                        </p>
                                    @endif
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>


        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const root = document.querySelector('.landingcard-swiper');
                const wrapper = root?.querySelector('.swiper-wrapper');
                const slides = Array.from(root?.querySelectorAll('.swiper-slide') ?? []);
                const cardsCount = slides.length;

                const SPACING_PX = 24; // gap 6 = 1.5rem
                let swiper = null;

                const spvFor = (w) => (w >= 1024) ? 4 : (w >= 768) ? 3 : (w >= 640) ? 2 : 1;

                const setAsFlexGrid = () => {
                    // destruir swiper si existe
                    if (swiper && swiper.destroy) {
                        swiper.destroy(true, true);
                        swiper = null;
                    }
                    // wrapper como flex centrado
                    wrapper.style.transform = ''; // quitar transform de swiper
                    wrapper.classList.add('!flex', '!flex-wrap', '!justify-center', 'gap-6');
                    // tamaño responsivo por tarjeta (4/3/2/1 cols) y que NO crezcan
                    slides.forEach(slide => {
                        slide.classList.add(
                            'grow-0', 'shrink-0',
                            'basis-full',
                            'sm:basis-[calc((100%-1.5rem)/2)]',
                            'md:basis-[calc((100%-1.5rem*2)/3)]',
                            'lg:basis-[calc((100%-1.5rem*3)/4)]'
                        );
                        // Swiper suele setear width inline, lo limpiamos
                        slide.style.width = '';
                    });
                };

                const setAsSwiper = () => {
                    // limpiar clases del modo flex
                    wrapper.classList.remove('!flex', '!flex-wrap', '!justify-center', 'gap-6');
                    slides.forEach(slide => {
                        slide.classList.remove(
                            'grow-0', 'shrink-0',
                            'basis-full',
                            'sm:basis-[calc((100%-1.5rem)/2)]',
                            'md:basis-[calc((100%-1.5rem*2)/3)]',
                            'lg:basis-[calc((100%-1.5rem*3)/4)]'
                        );
                    });

                    swiper = new Swiper('.landingcard-swiper', {
                        spaceBetween: SPACING_PX,
                        autoplay: {
                            delay: 4000,
                            disableOnInteraction: false
                        },
                        breakpoints: {
                            320: {
                                slidesPerView: 1,
                                spaceBetween: SPACING_PX
                            },
                            640: {
                                slidesPerView: 2,
                                spaceBetween: SPACING_PX
                            },
                            768: {
                                slidesPerView: 3,
                                spaceBetween: SPACING_PX
                            },
                            1024: {
                                slidesPerView: 4,
                                spaceBetween: SPACING_PX
                            },
                        },
                        loop: true
                    });
                };

                const equalizeHeights = () => {
                    const cards = document.querySelectorAll('.landingcard-swiper .swiper-slide a');
                    let maxH = 0;
                    cards.forEach(c => {
                        c.style.height = 'auto';
                        maxH = Math.max(maxH, c.offsetHeight);
                    });
                    cards.forEach(c => c.style.height = maxH + 'px');
                };

                const decide = () => {
                    const spv = spvFor(window.innerWidth);
                    if (cardsCount <= spv) {
                        setAsFlexGrid(); // sin Swiper, centrado
                    } else {
                        setAsSwiper(); // con Swiper
                    }
                    // igualar alturas en ambos modos
                    equalizeHeights();
                };

                // primera decisión
                decide();

                // reajustar en resize
                window.addEventListener('resize', () => decide());

                // recalcular cuando cargan imágenes (por si cambian alturas)
                const images = document.querySelectorAll('.landingcard-swiper img');
                images.forEach(img => img.addEventListener('load', () => equalizeHeights(), {
                    once: true
                }));
            });
        </script>

    @endif
@endif
