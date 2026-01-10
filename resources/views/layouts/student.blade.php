<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }} - Panel del alumno</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-tenant.favicon />

    @vite(['resources/css/student.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        :root {
            --ftt-color-base: {{ tenant_config('color_base', '#263d83') }};
            --ftt-color-dark: {{ tenant_config('color_dark', '#1d2d5e') }};
            --ftt-color-light: {{ tenant_config('color_light', '#f9fafb') }};
            --ftt-color-base-transparent: {{ tenant_config('color_base', '#263d83') }}55;
            --ftt-color-base-bright: {{ tenant_config('color_base', '#263d83') }}CC;
            --ftt-color-dark-transparent: {{ tenant_config('color_dark', '#263d83') }}55;
            --ftt-color-light-transparent: {{ tenant_config('color_light', '#fafafa') }}55;
            --ftt-color-text-footer: {{ tenant_config('footer_text_color', '#000000') }};
            --ftt-color-background-footer: {{ tenant_config('footer_background_color', '#ffffff') }}55;
        }
    </style>
</head>

<body class="text-gray-900 min-h-screen flex flex-col">

    <header class="student-header sticky top-0 z-10">
        <div class="max-w-6xl mx-auto flex justify-between items-center px-4 py-2 md:py-3">
            <div class="student-greeting text-base md:text-lg">
                ¡Hola, {{ auth()->user()->student->first_name ?? auth()->user()->name }}!
            </div>

            <nav class="flex items-center gap-4 text-xs md:text-sm">
                <a href="{{ route('tenant.student.dashboard') }}">Inicio</a>
                <a href="{{ route('tenant.student.progress') }}">Progreso</a>
                <a href="{{ route('tenant.student.messages') }}" class="relative">
                    Mensajes
                    @livewire(\App\Livewire\Tenant\Student\MessageBadge::class)
                </a>
                <a href="{{ route('tenant.student.payments') }}">Pagos</a>

               <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button type="submit" class="cursor-pointer">
                        Salir
                    </button>
                </form>

            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-6xl mx-auto w-full px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="text-center text-xs text-gray-400 py-3">
        FitTrack © {{ date('Y') }}
    </footer>

    @livewireScripts
</body>

</html>
