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

Te recordamos tu sesion de hoy del plan **{{ $planName }}**.

@if($lastCompletedAt)
**Ultima sesion registrada:** {{ $lastCompletedAt }}
@endif

<p style="margin:24px 0; text-align:center;">
    <a href="{{ $workoutUrl }}"
       style="display:inline-block; background:{{ $colorDark }}; color:#ffffff; text-decoration:none; font-weight:600; padding:12px 20px; border-radius:8px;">
        Comenzar entrenamiento
    </a>
</p>

@if($pdfUrl)
Si queres repasar el plan completo: [Descargar PDF]({{ $pdfUrl }})
@endif

@slot('footer')
    @component('mail::footer')
        <span style="display:block; margin-bottom:6px;">
            Descarga la app Android: <a href="{{ $androidAppUrl }}">{{ $androidAppUrl }}</a>
        </span>
        {{ date('Y') }} {{ $tenantName }}
    @endcomponent
@endslot
@endcomponent
