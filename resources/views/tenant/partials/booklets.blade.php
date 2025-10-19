@if (tenant_config('landing_booklets_show'))
    @php
        $booklets = \App\Models\LandingBooklet::where('active', true)
            ->orderBy('order')
            ->get();
    @endphp
    @if (count($booklets) > 0)
        <div class="w-full"
            style="background-color: {{ tenant_config('color_base', '#263d83') }}CC;">
            <div class="max-w-6xl mx-auto py-8">


                @if (tenant_config('landing_booklets_title'))
                    <div class="text-center  mb-0">
                        <span class="text-2xl font-bold text-white">
                            {!! tenant_config('landing_booklets_title') !!}
                        </span>
                    </div>
                @endif
                @if (tenant_config('landing_booklets_subtitle'))
                    <div class="text-center mb-2">
                        <span class="text-sm text-white">
                            {!! tenant_config('landing_booklets_subtitle') !!}
                        </span>
                    </div>
                @endif

                <div class="swiper booklet-swiper">
                    <div class="swiper-wrapper">
                        @foreach ($booklets as $booklet)
                            <div class="swiper-slide flex flex-col items-center justify-center text-center">
                                <a href="{{ $booklet->link }}" target="{{ $booklet->target }}">
                                    <img src="{{ $booklet->getFirstMediaUrl('cover') }}" alt=""
                                        class="w-full h-32 object-contain mb-2  p-2 rounded" />
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                new Swiper('.booklet-swiper', {
                    slidesPerView: 5,
                    spaceBetween: 24,
                    loop: true,
                    autoplay: {
                        delay: 4000,
                        disableOnInteraction: false,
                    },
                    breakpoints: {
                        320: {
                            slidesPerView: 2
                        },
                        640: {
                            slidesPerView: 3
                        },
                        1024: {
                            slidesPerView: 5
                        },
                    }
                });
            });
        </script>
    @endif
@endif
