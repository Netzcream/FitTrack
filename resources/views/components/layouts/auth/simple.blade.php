<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <script>
            (function() {
                const root = document.documentElement;
                const saved = localStorage.theme;
                const system = window.matchMedia('(prefers-color-scheme: dark)').matches;
                root.classList.toggle('dark', saved ? saved === 'dark' : system);
                window.FitTheme = {
                    toggle() {
                        const dark = !root.classList.contains('dark');
                        root.classList.toggle('dark', dark);
                        localStorage.theme = dark ? 'dark' : 'light';
                    }
                };
            })();
        </script>
    </head>
    <body class="min-h-screen antialiased bg-gradient-to-br from-indigo-50 to-cyan-50 dark:from-slate-950 dark:to-slate-900 text-slate-900 dark:text-slate-100">
        <!-- Header minimalista -->
        <header class="px-6 py-4 bg-white/85 dark:bg-slate-900/75 backdrop-blur border-b border-gray-200 dark:border-slate-700">
            <div class="max-w-6xl mx-auto flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-2xl font-extrabold tracking-wide text-indigo-700 dark:text-indigo-500" wire:navigate>
                    <x-application-logo class="h-9 w-9" />
                    FitTrack
                </a>
                <button onclick="window.FitTheme?.toggle()"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition">
                    <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/>
                    </svg>
                    <svg class="w-5 h-5 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                    </svg>
                </button>
            </div>
        </header>

        <div class="flex min-h-[calc(100vh-73px)] flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="w-full max-w-md">
                <div class="bg-white/80 dark:bg-slate-800/50 backdrop-blur-md rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-slate-700">
                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
