<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />


<title>@yield('title', __('site.dashboard')) - {{ tenant()->name ?? 'FitTrack' }}</title>

<x-tenant.favicon />

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<style>
    :root {
        --ftt-color-base: {{ tenant_config('color_base', '#263d83') }};
        --ftt-color-dark: {{ tenant_config('color_dark', '#263d83') }};
        --ftt-color-light: {{ tenant_config('color_light', '#fafafa') }};
        --ftt-color-base-transparent: {{ tenant_config('color_base', '#263d83') }}55;
        --ftt-color-base-bright: {{ tenant_config('color_base', '#263d83') }}CC;
        --ftt-color-dark-transparent: {{ tenant_config('color_dark', '#263d83') }}55;
        --ftt-color-light-transparent: {{ tenant_config('color_light', '#fafafa') }}55;
    }
</style>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
