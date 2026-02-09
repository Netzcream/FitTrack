@component('mail::layout')
@php
    $tenantName = $tenantName ?? 'FitTrack';
    $studentFirstName = $studentFirstName ?? 'Alumno';
    $logoUrl = $logoUrl ?? null;
    $brandUrl = $brandUrl ?? (config('app.url') ?? env('APP_URL', 'https://fittrack.com.ar'));
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

# Hola {{ $studentFirstName }}!

Te recordamos tu sesion de hoy del plan **{{ $planName }}**.

@if($lastCompletedAt)
**Ultima sesion registrada:** {{ $lastCompletedAt }}
@endif

@component('mail::button', ['url' => $workoutUrl])
Comenzar entrenamiento
@endcomponent

@if($pdfUrl)
Si queres repasar el plan completo: [Descargar PDF]({{ $pdfUrl }})
@endif

@slot('footer')
    @component('mail::footer')
        {{ date('Y') }} {{ $tenantName }}
    @endcomponent
@endslot
@endcomponent
