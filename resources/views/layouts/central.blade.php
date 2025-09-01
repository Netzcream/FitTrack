<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <title>FitTrack para Personal Trainers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="icon" type="image/x-icon" href="{{ asset('storage/images/fittrack-icon-only.png') }}" />




    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script>
        (function() {
            const root = document.documentElement;
            const saved = localStorage.theme; // 'dark' | 'light' | undefined
            const system = window.matchMedia('(prefers-color-scheme: dark)').matches;
            root.classList.toggle('dark', saved ? saved === 'dark' : system);

            window.FitTheme = {
                toggle() {
                    const dark = !root.classList.contains('dark');
                    root.classList.toggle('dark', dark);
                    localStorage.theme = dark ? 'dark' : 'light';
                }
            };

            // Livewire SPA: en cada navegación, re-aplica según lo guardado
            window.addEventListener('livewire:navigated', () => {
                const s = localStorage.theme;
                root.classList.toggle('dark', s ? s === 'dark' : system);
            });
        })();
    </script>
</head>

<body
    class="min-h-screen flex flex-col antialiased
             bg-gradient-to-br from-indigo-50 to-cyan-50
             dark:from-slate-950 dark:to-slate-900
             text-slate-900 dark:text-slate-100">

    <!-- Header -->
    @include('central.partials.header')

    {{ $slot }}

    @include('central.partials.footer')

    @livewireScripts
</body>

</html>
