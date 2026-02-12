@component('mail::layout')
@php
    $tenantName = $tenantName ?? 'FitTrack';
    $studentFirstName = $studentFirstName ?? 'Alumno';
    $registrationUrl = $registrationUrl ?? '#';
    $logoUrl = $logoUrl ?? null;
    $brandUrl = $brandUrl ?? (config('app.url') ?? env('APP_URL', 'https://fittrack.com.ar'));
    $colorBase = $colorBase ?? '#263d83';
    $colorDark = $colorDark ?? '#1d2d5e';
    $colorLight = $colorLight ?? '#f9fafb';
    $androidAppUrl = $androidAppUrl ?? 'https://repository.netzcream.com.ar/fittrack/FitTrack.apk';
@endphp

@slot('header')
    @component('mail::header', ['url' => $brandUrl])
@if($logoUrl)
<img src="{{ $logoUrl }}" height="50" alt="{{ $tenantName }}">
@else
{{ $tenantName }}
@endif
    @endcomponent
@endslot

# Hola {{ $studentFirstName }}!

Tu entrenador creo tu cuenta en {{ $tenantName }}.

Para activar tu acceso, primero debes definir tu clave.

<p style="margin:24px 0; text-align:center;">
    <a href="{{ $registrationUrl }}"
       style="display:inline-block; background:{{ $colorDark }}; color:#ffffff; text-decoration:none; font-weight:600; padding:12px 20px; border-radius:8px;">
        Definir mi clave
    </a>
</p>

Luego podras ingresar y ver tus planes de entrenamiento, progreso y mensajes.

Saludos,
{{ $tenantName }}

---

Si estas teniendo problemas al hacer clic en el boton "Definir mi clave", copia y pega la URL de abajo en tu navegador web:

[{{ $registrationUrl }}]({{ $registrationUrl }})

@slot('footer')
    @component('mail::footer')
        <span style="display:block; margin-bottom:6px;">
            Descarga la app Android: <a href="{{ $androidAppUrl }}">{{ $androidAppUrl }}</a>
        </span>
        {{ date('Y') }} {{ $tenantName }}
    @endcomponent
@endslot
@endcomponent
