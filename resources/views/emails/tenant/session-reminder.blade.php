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
    $workoutUrl = $workoutUrl ?? '#';
    $pdfUrl = $pdfUrl ?? null;
    $lastCompletedAt = $lastCompletedAt ?? null;
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
        Te recordamos tu sesion de hoy del plan <strong>{{ $planName }}</strong>.
    </div>

    @if($lastCompletedAt)
        <div style="font-size: 15px; line-height: 1.6; color: #4a5568; margin-top: 8px;">
            Ultima sesion registrada: {{ $lastCompletedAt }}
        </div>
    @endif
</div>

<div style="margin: 24px 0; text-align: center;">
    <a href="{{ $workoutUrl }}" style="background-color: {{ $colorBase }}; border-radius: 6px; color: #ffffff; display: inline-block; font-size: 16px; line-height: 1; padding: 14px 24px; text-decoration: none; font-weight: 600;">
        Comenzar entrenamiento
    </a>
</div>

@if($pdfUrl)
    <div style="font-size: 15px; line-height: 1.6; color: #4a5568; margin-bottom: 16px;">
        Si queres repasar el plan completo:
        <a href="{{ $pdfUrl }}" style="color: {{ $colorBase }};">Descargar PDF</a>
    </div>
@endif

@slot('footer')
    @component('mail::footer')
        {{ date('Y') }} {{ $tenantName }}
    @endcomponent
@endslot
@endcomponent
