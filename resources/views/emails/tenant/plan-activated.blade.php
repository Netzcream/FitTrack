@component('mail::layout')
@php
    $tenantName = $tenantName ?? 'FitTrack';
    $studentFirstName = $studentFirstName ?? 'Alumno';
    $logoUrl = $logoUrl ?? null;
    $brandUrl = $brandUrl ?? (config('app.url') ?? env('APP_URL', 'https://fittrack.com.ar'));
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

# Hola {{ $studentFirstName }}!

@if($wasAutomatic)
Tu plan **{{ $planName }}** ya esta activo.
@else
Te asignamos un nuevo plan: **{{ $planName }}**.
@endif

@if($startsAt)
**Inicio:** {{ $startsAt }}
@endif

@if($endsAt)
**Fin:** {{ $endsAt }}
@endif

@if($durationDays !== null)
**Duracion:** {{ $durationDays }} dias
@endif

@component('mail::button', ['url' => $planUrl])
Ver mi plan
@endcomponent

@if($pdfUrl)
Tambien podes descargarlo en PDF: [Descargar PDF]({{ $pdfUrl }})
@endif

Vamos por esa semana de entrenamiento.

@slot('footer')
    @component('mail::footer')
        {{ date('Y') }} {{ $tenantName }}
    @endcomponent
@endslot
@endcomponent
