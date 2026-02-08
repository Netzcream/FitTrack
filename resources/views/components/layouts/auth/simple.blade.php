<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <script>
            // Central auth is always rendered in light mode.
            document.documentElement.classList.remove('dark');
            window.addEventListener('livewire:navigated', () => {
                document.documentElement.classList.remove('dark');
            });
        </script>
    </head>
    <body class="min-h-screen antialiased bg-gradient-to-br from-indigo-50 to-cyan-50 text-slate-900">
        <!-- Header minimalista -->
        <header class="px-6 py-4 bg-white/85 backdrop-blur border-b border-gray-200">
            <div class="max-w-6xl mx-auto flex items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-2xl font-extrabold tracking-wide text-indigo-700" wire:navigate>
                    <x-application-logo class="h-9 w-9" />
                    FitTrack
                </a>
            </div>
        </header>

        <div class="flex min-h-[calc(100vh-73px)] flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="w-full max-w-md">
                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl p-8 border border-gray-200">
                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
