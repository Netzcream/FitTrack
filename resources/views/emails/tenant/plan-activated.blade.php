@component('mail::layout')
@php
    $tenantName = $tenantName ?? 'FitTrack';
    $studentFirstName = $studentFirstName ?? 'Alumno';
    $logoUrl = $logoUrl ?? null;
    $brandUrl = $brandUrl ?? (config('app.url') ?? env('APP_URL', 'https://fittrack.com.ar'));
    $colorBase = $colorBase ?? '#263d83';
    $colorDark = $colorDark ?? '#1d2d5e';
    $colorLight = $colorLight ?? '#f9fafb';
    $planName = $planName ?? 'Plan';
    $startsAt = $startsAt ?? null;
    $endsAt = $endsAt ?? null;
    $durationDays = $durationDays ?? null;
    $wasAutomatic = $wasAutomatic ?? false;
    $planUrl = $planUrl ?? '#';
    $pdfUrl = $pdfUrl ?? null;
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
        @if($wasAutomatic)
            Tu plan <strong>{{ $planName }}</strong> ya esta activo.
        @else
            Te asignamos un nuevo plan: <strong>{{ $planName }}</strong>.
        @endif
    </div>

    @if($startsAt)
        <div style="font-size: 15px; line-height: 1.6; color: #4a5568; margin-top: 8px;">
            Inicio: {{ $startsAt }}
        </div>
    @endif

    @if($endsAt)
        <div style="font-size: 15px; line-height: 1.6; color: #4a5568;">
            Fin: {{ $endsAt }}
        </div>
    @endif

    @if($durationDays !== null)
        <div style="font-size: 15px; line-height: 1.6; color: #4a5568;">
            Duracion: {{ $durationDays }} dias
        </div>
    @endif
</div>

<div style="margin: 24px 0; text-align: center;">
    <a href="{{ $planUrl }}" style="background-color: {{ $colorBase }}; border-radius: 6px; color: #ffffff; display: inline-block; font-size: 16px; line-height: 1; padding: 14px 24px; text-decoration: none; font-weight: 600;">
        Ver mi plan
    </a>
</div>

@if($pdfUrl)
    <div style="font-size: 15px; line-height: 1.6; color: #4a5568; margin-bottom: 16px;">
        Tambien podes descargarlo en PDF:
        <a href="{{ $pdfUrl }}" style="color: {{ $colorBase }};">Descargar PDF</a>
    </div>
@endif

<div style="font-size: 16px; line-height: 1.6; color: #4a5568;">
    Vamos por esa semana de entrenamiento.
</div>

@slot('footer')
    @component('mail::footer')
        {{ date('Y') }} {{ $tenantName }}
    @endcomponent
@endslot
@endcomponent
