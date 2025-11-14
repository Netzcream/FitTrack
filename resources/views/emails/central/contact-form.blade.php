@component('mail::layout')
{{-- Header --}}
@slot('header')
    @component('mail::header', ['url' => 'https://' . tenant('id') . '.' . env('APP_DOMAIN')])
        @if(tenant('logo_url'))
            <img src="{{ tenant('logo_url') }}" height="50" alt="{{ tenant('name') }}">
        @else
            {{ tenant('name') ?? config('app.name') }}
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
        © {{ date('Y') }} {{ $tenantName }}. Todos los derechos reservados.<br>
        Contacto: {{ $contactEmail }}
    @endcomponent
@endslot
@endcomponent
