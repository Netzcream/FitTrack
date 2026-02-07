@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => 'https://' . tenant('id') . '.' . env('APP_DOMAIN')])
            @if(tenant()->logo_tenant_url )
                <img src="{{ tenant()->logo_tenant_url  }}" height="50" alt="{{ tenant('name') }}">
            @else
                {{ tenant('name') ?? config('app.name') }}
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
