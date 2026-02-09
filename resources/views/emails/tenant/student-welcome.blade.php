@component('mail::layout')
@php
    $tenantName = $tenantName ?? 'FitTrack';
    $studentFirstName = $studentFirstName ?? 'Alumno';
    $registrationUrl = $registrationUrl ?? '#';
    $logoUrl = $logoUrl ?? null;
    $brandUrl = $brandUrl ?? (config('app.url') ?? env('APP_URL', 'https://fittrack.com.ar'));
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

@component('mail::button', ['url' => $registrationUrl])
Definir mi clave
@endcomponent

Luego podras ingresar y ver tus planes de entrenamiento, progreso y mensajes.

Saludos,  
{{ $tenantName }}

---

Si estas teniendo problemas al hacer clic en el boton "Definir mi clave", copia y pega la URL de abajo en tu navegador web:

[{{ $registrationUrl }}]({{ $registrationUrl }})

@slot('footer')
    @component('mail::footer')
        {{ date('Y') }} {{ $tenantName }}
    @endcomponent
@endslot
@endcomponent
