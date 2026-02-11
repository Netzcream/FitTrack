@component('mail::layout')
@php
    $tenantName = $tenantName ?? 'FitTrack';
    $studentFirstName = $studentFirstName ?? 'Alumno';
    $logoUrl = $logoUrl ?? null;
    $brandUrl = $brandUrl ?? (config('app.url') ?? env('APP_URL', 'https://fittrack.com.ar'));
    $colorBase = $colorBase ?? '#263d83';
    $colorDark = $colorDark ?? '#1d2d5e';
    $colorLight = $colorLight ?? '#f9fafb';
    $completedAt = $completedAt ?? null;
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

<div style="border-radius:10px; border:1px solid {{ $colorBase }}33; background: {{ $colorLight }}; padding:16px;">
    <p style="margin:0 0 8px 0; color:#111827; font-size:18px;"><strong>Hola {{ $studentFirstName }}!</strong></p>
    <p style="margin:0; color:#475569; font-size:15px;">
        Tu clave fue actualizada correctamente en {{ $tenantName }}.
    </p>
</div>

@if($completedAt)
<p style="margin:16px 0; color:#475569;">
    <strong style="color:#111827;">Fecha:</strong> {{ $completedAt }}
</p>
@endif

Si no realizaste este cambio, contacta a tu entrenador lo antes posible.

@slot('footer')
    @component('mail::footer')
        {{ date('Y') }} {{ $tenantName }}
    @endcomponent
@endslot
@endcomponent
