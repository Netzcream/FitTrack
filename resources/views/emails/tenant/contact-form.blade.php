@component('mail::layout')
@php
    $tenantName = $tenantName ?? (tenant('name') ?? config('app.name'));
    $contactEmail = $contactEmail ?? config('mail.from.address', 'notifications@fittrack.com.ar');
    $brandUrl = $brandUrl ?? (config('app.url') ?? env('APP_URL', 'https://fittrack.com.ar'));
    $logoUrl = $logoUrl ?? null;
@endphp
{{-- Header --}}
@slot('header')
    @component('mail::header', ['url' => $brandUrl])
@if($logoUrl)
<img src="{{ $logoUrl }}" height="50" alt="{{ $tenantName }}">
@else
{{ $tenantName }}
@endif
    @endcomponent
@endslot

{{-- Body --}}
# Nuevo mensaje de contacto

**Nombre:** {{ $name }}<br>
**Email:** [{{ $email }}](mailto:{{ $email }})<br>
**Teléfono:** {{ $mobile }}<br>

**Mensaje recibido:**

@component('mail::panel')
{{ $messageContent }}
@endcomponent

@component('mail::button', ['url' => 'mailto:' . $email])
Responder al Cliente
@endcomponent

---

Gracias,<br>
Equipo de {{ $tenantName }}

{{-- Footer --}}
@slot('footer')
    @component('mail::footer')
        {{ date('Y') }} {{ $tenantName }}. <br>
        Contacto: {{ $contactEmail }}
    @endcomponent
@endslot
@endcomponent
