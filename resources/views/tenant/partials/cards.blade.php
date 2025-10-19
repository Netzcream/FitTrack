@if (tenant_config('landing_cards_show'))
    @php
        $cards = \App\Models\LandingCard::where('active', true)->orderBy('order')->get();
    @endphp
    @if (count($cards))

        <div class="w-full py-12">
            {{-- Título y subtítulo --}}
            @if (tenant_config('landing_cards_title'))
                <div class="text-center mb-2">
                    <span class="text-2xl font-bold"
                          style="color: {{ tenant_config('color_base', '#263d83') }};">
                        {!! tenant_config('landing_cards_title') !!}
                    </span>
                </div>
            @endif
            @if (tenant_config('landing_cards_subtitle'))
                <div class="text-center mb-8">
                    <span class="text-sm text-gray-600">
                        {!! tenant_config('landing_cards_subtitle') !!}
                    </span>
                </div>
            @endif

            {{-- Contenedor principal --}}
            <div class="max-w-6xl mx-auto text-center">
                <div class="swiper landingcard-swiper">
                    <div class="swiper-wrapper">
                        @foreach ($cards as $card)
                            <div class="swiper-slide flex">
                                <a href="{{ $card->link }}" target="{{ $card->target }}"
                                   class="flex flex-col text-left bg-white rounded-xl overflow-hidden shadow transition-all hover:shadow-lg hover:-translate-y-1">
                                    {{-- Imagen rectangular arriba --}}
                                    @if ($card->getFirstMediaUrl('cover'))
                                        <div class="w-full h-40 overflow-hidden">
                                            <img src="{{ $card->getFirstMediaUrl('cover') }}" alt=""
                                                 class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
                                        </div>
                                    @endif

                                    {{-- Contenido textual --}}
                                    <div class="flex-1 flex flex-col justify-between p-6">
                                        <div>
                                            <h3 class="text-lg font-semibold mb-2"
                                                style="color: {{ tenant_config('color_base', '#263d83') }};">
                                                {!! $card->title !!}
                                            </h3>
                                            @if ($card->text)
                                                <p class="text-sm text-gray-600 leading-relaxed">
                                                    {!! $card->text !!}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Script Swiper y fallback flex-grid --}}
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const root = document.querySelector('.landingcard-swiper');
                const wrapper = root?.querySelector('.swiper-wrapper');
                const slides = Array.from(root?.querySelectorAll('.swiper-slide') ?? []);
                const cardsCount = slides.length;

                const SPACING_PX = 24;
                let swiper = null;

                const spvFor = (w) => (w >= 1024) ? 4 : (w >= 768) ? 3 : (w >= 640) ? 2 : 1;

                const setAsFlexGrid = () => {
                    if (swiper && swiper.destroy) {
                        swiper.destroy(true, true);
                        swiper = null;
                    }
                    wrapper.style.transform = '';
                    wrapper.classList.add('!flex', '!flex-wrap', '!justify-center', 'gap-6');
                    slides.forEach(slide => {
                        slide.classList.add(
                            'grow-0', 'shrink-0',
                            'basis-full',
                            'sm:basis-[calc((100%-1.5rem)/2)]',
                            'md:basis-[calc((100%-1.5rem*2)/3)]',
                            'lg:basis-[calc((100%-1.5rem*3)/4)]'
                        );
                        slide.style.width = '';
                    });
                };

                const setAsSwiper = () => {
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
                        setAsFlexGrid();
                    } else {
                        setAsSwiper();
                    }
                    equalizeHeights();
                };

                decide();
                window.addEventListener('resize', () => decide());
                const images = document.querySelectorAll('.landingcard-swiper img');
                images.forEach(img => img.addEventListener('load', () => equalizeHeights(), { once: true }));
            });
        </script>

    @endif
@endif
