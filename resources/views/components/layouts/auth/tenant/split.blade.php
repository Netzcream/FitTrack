<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('tenant.partials.head', ['title' => 'Ingresar'])
    <script>
        // Tenant auth is always rendered in light mode.
        document.documentElement.classList.remove('dark');
        window.addEventListener('livewire:navigated', () => {
            document.documentElement.classList.remove('dark');
        });
    </script>
</head>

<style>
    :root {
        --lnq-color-base: {{ $healthcare?->hcConfig('site_color_base') ?? tenant_config('color_base', '#263d83') }};
        --lnq-color-dark: {{ $healthcare?->hcConfig('site_color_dark') ?? tenant_config('color_dark', '#1e2f63') }};
        --lnq-color-light: {{ $healthcare?->hcConfig('site_color_light') ?? tenant_config('color_light', '#fafafa') }};
        --lnq-color-base-cc: {{ $healthcare?->hcConfig('site_color_base') ?? tenant_config('color_base', '#263d83') }}CC;
        --lnq-color-base-55: {{ $healthcare?->hcConfig('site_color_base') ?? tenant_config('color_base', '#263d83') }}55;
        --lnq-color-dark-cc: {{ $healthcare?->hcConfig('site_color_dark') ?? tenant_config('color_dark', '#1e2f63') }}CC;
        --lnq-color-light-55: {{ $healthcare?->hcConfig('site_color_light') ?? tenant_config('color_light', '#fafafa') }}55;
        --lnq-text-footer: {{ $healthcare?->hcConfig('site_footer_text_color') ?? tenant_config('footer_text_color', '#000000') }};
        --lnq-bg-footer: {{ $healthcare?->hcConfig('site_footer_background_color') ?? tenant_config('footer_background_color', '#ffffff') }};
    }

    .tenant-primary {
        background-color: var(--lnq-color-base);
        border-color: var(--lnq-color-base);
        color: #ffffff;
    }

    .tenant-primary:hover {
        background-color: var(--lnq-color-dark);
        border-color: var(--lnq-color-dark);
    }
</style>

<body class="min-h-screen antialiased bg-slate-50">
    <div
        class="relative grid h-dvh flex-col place-items-stretch px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">

        {{-- Columna izquierda (branding/quote) --}}
        <div class="relative hidden h-full flex-col p-10 text-white lg:flex border-e border-white/10">
            {{-- Imagen de fondo full cover --}}
            @php
                $cover = 'https://placehold.co/1920x1080?text=' . tenant()->name;

                $coverUrl = tenant()->config?->getFirstMediaUrl('cover') ?: $cover;
            @endphp
            <div class="absolute inset-0">
                <img src="{{ $coverUrl ?? 'https://placehold.co/800x1200?text=Cover' }}" alt="Cover"
                    class="w-full h-full object-cover" />
                {{-- Overlay de color base semitransparente --}}
                <div class="absolute inset-0" style="background-color: var(--lnq-color-base-55);"></div>
            </div>



            {{--
            @php
                [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
            @endphp

            <div class="relative z-20 mt-auto">
                <blockquote class="space-y-2">
                    <flux:heading size="lg">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                    <footer>
                        <flux:heading>{{ trim($author) }}</flux:heading>
                    </footer>
                </blockquote>
            </div>
            --}}
        </div>


        {{-- Columna derecha (form) --}}
        <div class="h-full w-full bg-white lg:p-8">
            <div class="mx-auto flex min-h-full w-full flex-col justify-center space-y-6 sm:w-[350px] text-slate-900">
                <a href="{{ route('tenant.landing') }}" class="z-20 flex flex-col items-center gap-2 font-medium "
                    wire:navigate>
                    <span class="flex h-10 mb-6 items-center justify-center rounded-md">
                        <x-tenant.app-logo-icon class="me-2 h-24 fill-current text-[color:var(--lnq-color-base)]" />
                    </span>

                </a>
                <div class="tenant-accent">
                    {{ $slot }}
                </div>
                <style>
                    .tenant-accent a,
                    a.tenant-accent {
                        color: var(--lnq-color-base);
                    }

                    .tenant-accent a:hover,
                    a.tenant-accent:hover {
                        color: var(--lnq-color-dark);
                    }
                </style>
            </div>
        </div>
    </div>

    @fluxScripts
</body>

</html>
