@component('mail::layout')
@php
    $tenantName = $tenantName ?? 'FitTrack';
    $logoUrl = $logoUrl ?? null;
    $brandUrl = $brandUrl ?? (config('app.url') ?? env('APP_URL', 'https://fittrack.com.ar'));
    $colorBase = $colorBase ?? '#263d83';
    $colorDark = $colorDark ?? '#1d2d5e';
    $colorLight = $colorLight ?? '#f9fafb';
    $resetUrl = $resetUrl ?? '#';
    $expireMinutes = $expireMinutes ?? 60;
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
    <p style="margin:0 0 8px 0; color:#111827; font-size:18px;"><strong>Hola!</strong></p>
    <p style="margin:0; color:#475569; font-size:15px;">
        Recibimos una solicitud para restablecer la clave de tu cuenta en {{ $tenantName }}.
    </p>
</div>

<p style="margin:24px 0; text-align:center;">
    <a href="{{ $resetUrl }}"
       style="display:inline-block; background:{{ $colorDark }}; color:#ffffff; text-decoration:none; font-weight:600; padding:12px 20px; border-radius:8px;">
        Restablecer clave
    </a>
</p>

Este enlace de restablecimiento expirara en **{{ $expireMinutes }} minutos**.

Si no solicitaste este cambio, puedes ignorar este correo.

Saludos,  
{{ $tenantName }}

---

Si no funciona el boton "Restablecer clave", copia y pega esta URL en tu navegador:

[{{ $resetUrl }}]({{ $resetUrl }})

@slot('footer')
    @component('mail::footer')
        {{ date('Y') }} {{ $tenantName }}
    @endcomponent
@endslot
@endcomponent
