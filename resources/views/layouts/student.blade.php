{{-- resources/views/layouts/student.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }} — Panel del alumno</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">

    <header class="bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-6xl mx-auto flex justify-between items-center px-4 py-3">
            <div class="font-semibold text-green-700 text-lg">
                ¡Hola, {{ auth()->user()->student->first_name ?? auth()->user()->name }}!
            </div>
            <nav class="flex items-center gap-5 text-sm text-gray-600">
                <a href="{{ route('tenant.student.dashboard') }}" class="hover:text-green-700">Inicio</a>
                <a href="{{ route('tenant.student.progress') }}" class="hover:text-green-700">Progreso</a>
                <a href="{{ route('tenant.student.workout-today') }}" class="hover:text-green-700">Rutina</a>
                <a href="{{ route('tenant.student.messages') }}" class="hover:text-green-700">Mensajes</a>
                <a href="{{ route('tenant.student.payments') }}" class="hover:text-green-700">Pagos</a>
                <a href="{{ route('tenant.logout') }}" class="hover:text-red-600">Salir</a>
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-6xl mx-auto w-full px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="text-center text-xs text-gray-400 py-4">
        FitTrack © {{ date('Y') }}
    </footer>

    @livewireScripts
</body>
</html>
