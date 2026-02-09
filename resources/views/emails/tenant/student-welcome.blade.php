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

<div style="font-size: 24px; font-weight: 700; color: {{ $colorDark }}; margin-bottom: 16px;">
    Hola {{ $studentFirstName }}!
</div>

<div style="background-color: {{ $colorLight }}; border-radius: 10px; padding: 18px;">
    <div style="font-size: 16px; line-height: 1.6; color: #4a5568;">
        Tu entrenador creo tu cuenta en {{ $tenantName }}.
    </div>

    <div style="font-size: 16px; line-height: 1.6; color: #4a5568; margin-top: 10px;">
        Para activar tu acceso, primero debes definir tu clave.
    </div>
</div>

<div style="margin: 24px 0; text-align: center;">
    <a href="{{ $registrationUrl }}" style="background-color: {{ $colorBase }}; border-radius: 6px; color: #ffffff; display: inline-block; font-size: 16px; line-height: 1; padding: 14px 24px; text-decoration: none; font-weight: 600;">
        Definir mi clave
    </a>
</div>

<div style="font-size: 16px; line-height: 1.6; color: #4a5568;">
    Luego podras ingresar y ver tus planes de entrenamiento, progreso y mensajes.
</div>

<div style="font-size: 16px; line-height: 1.6; color: #4a5568; margin-top: 16px;">
    Saludos,<br>
    {{ $tenantName }}
</div>

<hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;">

<div style="font-size: 13px; line-height: 1.6; color: #6b7280;">
    Si estas teniendo problemas al hacer clic en el boton "Definir mi clave", copia y pega la URL de abajo en tu navegador web:
</div>

<div style="font-size: 13px; line-height: 1.6; word-break: break-all; margin-top: 8px;">
    <a href="{{ $registrationUrl }}" style="color: {{ $colorBase }};">{{ $registrationUrl }}</a>
</div>

@slot('footer')
    @component('mail::footer')
        {{ date('Y') }} {{ $tenantName }}
    @endcomponent
@endslot
@endcomponent
