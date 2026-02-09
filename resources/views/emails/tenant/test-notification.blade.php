@component('mail::layout')
@php
    $tenantName = $tenantName ?? (tenant('name') ?? config('app.name'));
    $brandUrl = $brandUrl ?? (config('app.url') ?? env('APP_URL', 'https://fittrack.com.ar'));
    $logoUrl = $logoUrl ?? null;
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

# Verificación de correo de notificaciones

**Canal probado:** {{ $channel }}
**Correo probado:** [{{ $testedEmail }}](mailto:{{ $testedEmail }})

**Motivo:** {{ $reason }}

@component('mail::panel')
Este es un mensaje de verificación enviado para confirmar que la configuración de envío y el
correo de destino funcionan correctamente para el canal de **{{ $channel }}**.
@endcomponent

Enviado por {{ $tenantName }} el {{ now()->format('Y-m-d H:i:s') }}.

@slot('footer')
    @component('mail::footer')
        {{ date('Y') }} {{ $tenantName }}.
    @endcomponent
@endslot
@endcomponent
