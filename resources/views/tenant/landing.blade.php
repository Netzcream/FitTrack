<x-layouts.tenant.guest>

    <style>
        :root {
            --ftt-color-base: {{ tenant_config('color_base', '#263d83') }};
            --ftt-color-dark: {{ tenant_config('color_dark', '#263d83') }};
            --ftt-color-light: {{ tenant_config('color_light', '#fafafa') }};
            --ftt-color-base-transparent: {{ tenant_config('color_base', '#263d83') }}55;
            --ftt-color-base-bright: {{ tenant_config('color_base', '#263d83') }}CC;
            --ftt-color-dark-transparent: {{ tenant_config('color_dark', '#263d83') }}55;
            --ftt-color-light-transparent: {{ tenant_config('color_light', '#fafafa') }}55;
            --ftt-color-text-footer: {{ tenant_config('footer_text_color', '#000000') }};
            --ftt-color-background-footer: {{ tenant_config('footer_background_color', '#ffffff') }}55;
        }

        .btn-outline-white:hover {
            color: var(--ftt-color-base) !important;
        }

        .card-colored {
            background-color: var(--ftt-color-base);
        }

        .card-colored:hover {
            background-color: var(--ftt-color-dark);
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <div class="absolute top-4 right-4 z-20 flex gap-2">
        @if (Route::has('tenant.login'))
            @auth


                @hasanyrole('Admin|Asistente|Entrenador')
                    <a href="{{ route('tenant.dashboard') }}" class="btn-outline-white flex items-center rounded-full">
                        <x-icons.lucide.layout-dashboard class="w-4 h-4 mr-1" />
                        Gestión
                    </a>
                @endhasanyrole
                @hasrole('Alumno')
                    <a href="{{ route('tenant.student.dashboard') }}" class="btn-outline-white flex items-center rounded-full">
                        <x-icons.lucide.user-round class="w-4 h-4 mr-1" />
                        Área Alumno
                    </a>
                @endhasrole


                <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button type="submit" class="btn-outline-white flex items-center cursor-pointer rounded-full">
                        <x-icons.lucide.log-out class="w-4 h-4 mr-1" />
                        Salir
                    </button>
                </form>
            @else
                <a href="{{ route('tenant.login') }}" class="btn-outline-white rounded-full">
                    <i class="fa-solid fa-user mr-1"></i> Ingresar
                </a>
            @endauth
        @endif
    </div>

    @php
        $cover = 'https://placehold.co/1920x1080?text=' . tenant()->name;
        $coverUrl = tenant()->config?->getFirstMediaUrl('cover') ?: $cover;
    @endphp




    <div class="relative h-[800px] md:h-[600px] bg-cover bg-center flex items-center justify-center"
        style="background-image: url('{{ $coverUrl }}');">

        {{-- Overlay con gradiente sutil --}}
        <div
            class="absolute inset-0 bg-gradient-to-b from-[var(--ftt-color-base-bright)]/80 to-[var(--ftt-color-dark-transparent)]/90">
        </div>

        {{-- Contenido centrado --}}
        <div class="relative z-10 text-center text-white px-6">
            @if (tenant_config('landing_title'))
                <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight mb-4 drop-shadow-lg">
                    {{ tenant_config('landing_title') }}
                </h1>
            @endif

            @if (tenant_config('landing_subtitle'))
                <p class="text-2xl md:text-3xl font-medium text-white/90 max-w-3xl mx-auto leading-relaxed">
                    {{ tenant_config('landing_subtitle') }}
                </p>
            @endif

            @if (tenant_config('landing_general_show_form'))
                <a href="#contacto"
                    class="inline-block mt-10 px-8 py-3 bg-white/80 text-[var(--ftt-color-base)] font-semibold rounded-xl shadow-lg hover:bg-white transition">
                    Quiero que me contacten
                </a>
            @endif
        </div>
    </div>








    @include('tenant.partials.logo')

    @include('tenant.partials.banners')

    @include('tenant.partials.cards')

    @if (tenant_config('landing_general_show_form'))
        <section id="contacto" class="relative py-24 overflow-hidden">
            {{-- Fondo suave que se integra con el footer --}}
            <div
                class="absolute inset-0 bg-gradient-to-b from-[var(--ftt-color-light)] to-[var(--ftt-color-base-transparent)] pointer-events-none">
            </div>

            <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Mismo ancho visual que tu versión buena (casi todo el contenedor) --}}
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-10 sm:p-12">
                    <h2 class="text-4xl font-bold text-center mb-10 text-[var(--ftt-color-base)]">
                        Te asesoramos
                    </h2>
                    <livewire:tenant.contact-form />
                </div>
            </div>
        </section>
    @endif





    @include('tenant.partials.booklets')

    @if (tenant_config('landing_whatsapp'))
        <a href="https://wa.me/{{ tenant_config('landing_whatsapp') }}"
            class="fixed bottom-6 right-6 z-50 inline-flex items-center justify-center w-14 h-14 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-lg transition duration-300"
            target="_blank" rel="noopener noreferrer" aria-label="Contactar por WhatsApp">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.198.297-.767.966-.94 1.164-.173.198-.347.223-.644.075-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.372-.025-.52-.075-.149-.669-1.611-.916-2.203-.242-.579-.487-.5-.669-.51-.173-.008-.372-.01-.571-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.412.248-.694.248-1.289.173-1.412-.074-.124-.272-.198-.57-.347m-5.421 7.617h-.001a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884a9.842 9.842 0 016.993 2.9 9.823 9.823 0 012.893 6.994c-.003 5.45-4.438 9.884-9.889 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .184 5.311.181 11.869c0 2.096.547 4.142 1.588 5.945L.057 24l6.293-1.654a11.85 11.85 0 005.699 1.448h.005c6.554 0 11.865-5.311 11.868-11.869a11.82 11.82 0 00-3.48-8.413" />
            </svg>
        </a>
    @endif


    @if (tenant_config('landing_footer'))
        <footer class="text-sm text-white"
            style="background: linear-gradient(180deg, var(--ftt-color-base) 0%, var(--ftt-color-dark) 100%);">
            <div
                class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-center sm:text-left opacity-90 leading-relaxed">
                    {!! tenant_config('landing_footer') !!}
                </div>
                <nav class="flex items-center gap-6 opacity-90">
                    <a href="#" class="hover:opacity-100 transition">Instagram</a>
                    @if (tenant_config('landing_whatsapp'))
                        <a href="https://wa.me/{{ tenant_config('landing_whatsapp') }}"
                            class="hover:opacity-100 transition">WhatsApp</a>
                    @endif
                </nav>
            </div>
        </footer>
    @endif



    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>

</x-layouts.tenant.guest>
