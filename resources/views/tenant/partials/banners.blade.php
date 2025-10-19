@if (tenant_config('landing_banners_show'))

    @php
        $banners = \App\Models\LandingBanner::where('active', true)
            ->orderBy('order')
            ->get();

        $bannerData = $banners->map(function ($banner) {
            $desktop = $banner->getFirstMediaUrl('cover') ?: asset('images/default-banner.jpg');
            $mobile = $banner->getFirstMediaUrl('cover_mobile') ?: $desktop;

            return [
                'uuid'          => $banner->uuid,
                'image'         => $desktop,
                'image_mobile'  => $mobile,
                'link'          => $banner->link,
                'target'        => $banner->target,
                'text'          => $banner->text,
            ];
        });
    @endphp

    @if ($banners->count() > 0)
        <div
            x-data="{
                activeIndex: 0,
                banners: @js($bannerData),
                interval: null,
                init() { this.startInterval() },
                startInterval() {
                    this.interval = setInterval(() => {
                        this.activeIndex = (this.activeIndex + 1) % this.banners.length;
                    }, 5000);
                },
                resetInterval() {
                    clearInterval(this.interval);
                    this.startInterval();
                }
            }"
            class="w-full h-[600px] sm:h-[500px] md:h-[550px] lg:h-[550px] xl:h-[600px] relative overflow-hidden"
        >

            <template x-for="(banner, index) in banners" :key="banner.uuid">
                <div
                    class="absolute inset-0 transition-opacity duration-700"
                    x-show="index === activeIndex"
                    x-transition:enter="opacity-0"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="opacity-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <a :href="banner.link" :target="banner.target" class="relative block w-full h-full group">
                        <picture>
                            <!-- Imagen mobile -->
                            <source :srcset="banner.image_mobile" media="(max-width: 768px)">
                            <!-- Imagen desktop -->
                            <img
                                :src="banner.image"
                                class="w-full h-full object-cover transition-opacity duration-500"
                                loading="lazy"
                                alt=""
                            />
                        </picture>

                        <template x-if="banner.text">
                            <div
                                class="absolute inset-0 bg-black/50 flex items-center justify-center px-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <p
                                    class="text-white text-center text-2xl sm:text-3xl md:text-5xl lg:text-6xl font-semibold leading-snug"
                                    x-text="banner.text">
                                </p>
                            </div>
                        </template>
                    </a>
                </div>
            </template>

            {{-- Flecha izquierda --}}
            <div class="absolute inset-y-0 left-0 flex items-center px-4">
                <button
                    @click="activeIndex = (activeIndex - 1 + banners.length) % banners.length; resetInterval()"
                    class="bg-black/40 text-white p-2 rounded-full hover:bg-black/60 transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            {{-- Flecha derecha --}}
            <div class="absolute inset-y-0 right-0 flex items-center px-4">
                <button
                    @click="activeIndex = (activeIndex + 1) % banners.length; resetInterval()"
                    class="bg-black/40 text-white p-2 rounded-full hover:bg-black/60 transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

        </div>
    @endif
@endif
