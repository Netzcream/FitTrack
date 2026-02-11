<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <title>FitTrack para Personal Trainers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/fittrack-logo.svg') }}" />




    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body
    class="min-h-screen flex flex-col antialiased
             bg-gradient-to-br from-indigo-50 to-cyan-50
             text-slate-900">

    <!-- Header -->
    @include('central.partials.header')

    {{ $slot }}

    @include('central.partials.footer')

    @livewireScripts
</body>

</html>

