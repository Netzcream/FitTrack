<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Próximamente - fittrack.com.ar</title>
    <script>
        /*tailwind.config = {
                                darkMode: 'media',
                            }*/
    </script>
    {{--
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='40' fill='%236A51CD' /%3E%3C/svg%3E">
--}}
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E
%3Cdefs%3E
%3ClinearGradient id='g' x1='0%25' y1='0%25' x2='100%25' y2='0%25'%3E
%3Cstop offset='0%25' stop-color='%23fdf6e3' /%3E
%3Cstop offset='50%25' stop-color='%233bc9f5' /%3E
%3Cstop offset='100%25' stop-color='%238141e8' /%3E
%3C/linearGradient%3E
%3C/defs%3E
%3Ccircle cx='50' cy='50' r='45' fill='url(%23g)' /%3E
%3C/svg%3E">


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;500;700&family=Playfair+Display:wght@500&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        h1 {
            font-family: 'Inter', sans-serif;
        }


        @keyframes floatSlow {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-3px);
                /* apenas perceptible */
            }
        }

        .floating-logo {
            animation: floatSlow 6s ease-in-out infinite;
        }

        .wave-letter {
            animation: wave 2s ease-in-out infinite;
            display: inline-block;
        }

        .wave-letter:nth-child(1) {
            animation-delay: 0s;
        }

        .wave-letter:nth-child(2) {
            animation-delay: 0.1s;
        }

        .wave-letter:nth-child(3) {
            animation-delay: 0.2s;
        }

        .wave-letter:nth-child(4) {
            animation-delay: 0.3s;
        }

        .wave-letter:nth-child(5) {
            animation-delay: 0.4s;
        }

        .wave-letter:nth-child(6) {
            animation-delay: 0.5s;
        }

        @keyframes wave {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-18px);
            }
        }
    </style>
</head>

<body
    class=" min-h-screen flex flex-col items-center justify-center px-6 transition-colors duration-500 bg-slate-900 text-slate-100">
    <div class="text-center max-w-2xl">

        <div class="absolute top-4 right-4 z-20 flex gap-2">
            @if (Route::has('login'))
                @auth
                    <!-- Botón Dashboard -->
                    <a href="{{ route('dashboard') }}"
                        class="text-white hover:text-slate-900 hover:bg-white bg-transparent border border-white/40 px-4 py-1.5 text-sm font-medium transition duration-150 ease-in-out">
                        <i class="fa-solid fa-table-columns mr-1"></i> Gestión
                    </a>

                    <!-- Botón Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="cursor-pointer text-white hover:text-slate-900 hover:bg-white bg-transparent border border-white/40 px-4 py-1.5 text-sm font-medium transition duration-150 ease-in-out">
                            <i class="fa-solid fa-right-from-bracket mr-1"></i> Salir
                        </button>
                    </form>
                @else
                    <!-- Botón Login -->
                    <a href="{{ route('login') }}"
                        class="text-white hover:text-[{{tenant_config('color_base','#263d83')}}] hover:bg-white bg-transparent border border-white/40 px-4 py-1.5 text-sm font-medium transition duration-150 ease-in-out">
                        <i class="fa-solid fa-user mr-1"></i> Ingresar
                    </a>


                @endauth
            @endif
        </div>


        <div class="floating-logo" style="width: 500px;">
            <svg viewBox="0 0 250 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#3bc9f5" />
                        <stop offset="100%" stop-color="#8141e8" />
                    </linearGradient>
                </defs>

                <circle cx="45" cy="44" r="20" fill="#fdf6e3" />

                <text x="75" y="60" font-family="Poppins, sans-serif" font-size="48" font-weight="600"
                    fill="url(#gradient)" textLength="137" lengthAdjust="spacing">
                    <tspan class="wave-letter">l</tspan>
                    <tspan class="wave-letter">u</tspan>
                    <tspan class="wave-letter">n</tspan>
                    <tspan class="wave-letter">i</tspan>
                    <tspan class="wave-letter">q</tspan>
                    <tspan class="wave-letter">o</tspan>
                </text>
            </svg>

        </div>




        {{--
        <h1 class="flex items-center mb-6 p-6 font-bold bg-gradient-to-r from-purple-500 to-blue-400 bg-clip-text text-transparent">
            <span class="text-[4rem] md:text-[6rem] leading-none me-2 align-middle">●</span>
            <span class="text-[3.75rem] md:text-[5.5rem] leading-none">fittrack</span>
          </h1>
          --}}
        <div
            class="inline-flex rounded-full bg-slate-200 dark:bg-slate-700 px-6 py-3 text-sm font-medium text-slate-600 dark:text-slate-200">
            Próximamente</div>
    </div>


    <div class="hidden">
        <span name="blue-marguerite"
            class="peer-focus:ring-blue-marguerite-300 dark:peer-focus:ring-blue-marguerite-800 peer-checked:bg-blue-marguerite-600 dark:peer-checked:bg-blue-marguerite-600 focus:ring-blue-marguerite-500 focus:border-blue-marguerite-500 dark:focus:ring-blue-marguerite-500 dark:focus:border-blue-marguerite-500 bg-blue-marguerite-400 hover:bg-blue-marguerite-600 focus:ring-blue-marguerite-300 text-blue-marguerite-400 dark:text-blue-marguerite-600 fill-blue-marguerite-500 text-blue-marguerite-500 dark:text-blue-marguerite-400 dark:group-hover:text-blue-marguerite-500 group-hover:text-blue-marguerite-900"></span>
        <span name="cork"
            class="peer-focus:ring-cork-300 dark:peer-focus:ring-cork-800 peer-checked:bg-cork-600 dark:peer-checked:bg-cork-600 focus:ring-cork-500 focus:border-cork-500 dark:focus:ring-cork-500 dark:focus:border-cork-500 bg-cork-400 hover:bg-cork-600 focus:ring-cork-300 text-cork-400 dark:text-cork-600 fill-cork-500 text-cork-500 dark:text-cork-400 dark:group-hover:text-cork-500 group-hover:text-cork-900"></span>
        <span name="green-life"
            class="peer-focus:ring-green-life-300 dark:peer-focus:ring-green-life-800 peer-checked:bg-green-life-600 dark:peer-checked:bg-green-life-600 focus:ring-green-life-500 focus:border-green-life-500 dark:focus:ring-green-life-500 dark:focus:border-green-life-500 bg-green-life-400 hover:bg-green-life-600 focus:ring-green-life-300 text-green-life-400 dark:text-green-life-600 fill-green-life-500 text-green-life-500 dark:text-green-life-400 dark:group-hover:text-green-life-500 group-hover:text-green-life-900"></span>
        <span name="blush-pink"
            class="peer-focus:ring-blush-pink-300 dark:peer-focus:ring-blush-pink-800 peer-checked:bg-blush-pink-600 dark:peer-checked:bg-blush-pink-600 focus:ring-blush-pink-500 focus:border-blush-pink-500 dark:focus:ring-blush-pink-500 dark:focus:border-blush-pink-500 bg-blush-pink-400 hover:bg-blush-pink-600 focus:ring-blush-pink-300 text-blush-pink-400 dark:text-blush-pink-600 fill-blush-pink-500 text-blush-pink-500 dark:text-blush-pink-400 dark:group-hover:text-blush-pink-500 group-hover:text-blush-pink-900"></span>
        <span name="celery"
            class="peer-focus:ring-celery-300 dark:peer-focus:ring-celery-800 peer-checked:bg-celery-600 dark:peer-checked:bg-celery-600 focus:ring-celery-500 focus:border-celery-500 dark:focus:ring-celery-500 dark:focus:border-celery-500 bg-celery-400 hover:bg-celery-600 focus:ring-celery-300 text-celery-400 dark:text-celery-600 fill-celery-500 text-celery-500 dark:text-celery-400 dark:group-hover:text-celery-500 group-hover:text-celery-900"></span>
        <span name="primary"
            class="peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 peer-checked:bg-primary-600 dark:peer-checked:bg-primary-600 focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 bg-primary-400 hover:bg-primary-600 focus:ring-primary-300 text-primary-400 dark:text-primary-600 fill-primary-500 text-primary-500 dark:text-primary-400 dark:group-hover:text-primary-500 group-hover:text-primary-900"></span>
        <span name="secondary"
            class="peer-focus:ring-secondary-300 dark:peer-focus:ring-secondary-800 peer-checked:bg-secondary-600 dark:peer-checked:bg-secondary-600 focus:ring-secondary-500 focus:border-secondary-500 dark:focus:ring-secondary-500 dark:focus:border-secondary-500 bg-secondary-400 hover:bg-secondary-600 focus:ring-secondary-300 text-secondary-400 dark:text-secondary-600 fill-secondary-500 text-secondary-500 dark:text-secondary-400 dark:group-hover:text-secondary-500 group-hover:text-secondary-900"></span>
        <span name="tertiary"
            class="peer-focus:ring-tertiary-300 dark:peer-focus:ring-tertiary-800 peer-checked:bg-tertiary-600 dark:peer-checked:bg-tertiary-600 focus:ring-tertiary-500 focus:border-tertiary-500 dark:focus:ring-tertiary-500 dark:focus:border-tertiary-500 bg-tertiary-400 hover:bg-tertiary-600 focus:ring-tertiary-300 text-tertiary-400 dark:text-tertiary-600 fill-tertiary-500 text-tertiary-500 dark:text-tertiary-400 dark:group-hover:text-tertiary-500 group-hover:text-tertiary-900"></span>
    </div>
</body>

</html>
