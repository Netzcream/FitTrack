@component('mail::layout')
@php
    $tenantName = $tenantName ?? 'FitTrack';
    $studentFirstName = $studentFirstName ?? 'Alumno';
    $logoUrl = $logoUrl ?? null;
    $brandUrl = $brandUrl ?? (config('app.url') ?? env('APP_URL', 'https://fittrack.com.ar'));
    $colorBase = $colorBase ?? '#263d83';
    $colorDark = $colorDark ?? '#1d2d5e';
    $colorLight = $colorLight ?? '#f9fafb';
    $amount = $amount ?? null;
    $paidAt = $paidAt ?? null;
    $paymentMethod = $paymentMethod ?? null;
    $reference = $reference ?? null;
    $planName = $planName ?? null;
    $invoiceNumber = $invoiceNumber ?? null;
    $paymentsUrl = $paymentsUrl ?? '#';
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

<div style="border-top:4px solid {{ $colorBase }}; padding-top:12px; margin-bottom:12px;">
    <p style="margin:0 0 8px 0; color:#111827; font-size:18px;"><strong>Hola {{ $studentFirstName }}!</strong></p>
    <p style="margin:0; color:#475569; font-size:15px;">
        Tu pago fue aplicado correctamente en {{ $tenantName }}.
    </p>
</div>

<table role="presentation" style="width:100%; margin:16px 0; border-collapse:collapse;">
    <tbody>
@if($invoiceNumber)
        <tr>
            <td style="padding:6px 0; color:#111827; font-weight:600;">Invoice</td>
            <td style="padding:6px 0; color:#475569; text-align:right;">{{ $invoiceNumber }}</td>
        </tr>
@endif
@if($amount)
        <tr>
            <td style="padding:6px 0; color:#111827; font-weight:600;">Monto</td>
            <td style="padding:6px 0; color:#475569; text-align:right;">{{ $amount }}</td>
        </tr>
@endif
@if($paidAt)
        <tr>
            <td style="padding:6px 0; color:#111827; font-weight:600;">Fecha</td>
            <td style="padding:6px 0; color:#475569; text-align:right;">{{ $paidAt }}</td>
        </tr>
@endif
@if($paymentMethod)
        <tr>
            <td style="padding:6px 0; color:#111827; font-weight:600;">Metodo</td>
            <td style="padding:6px 0; color:#475569; text-align:right;">{{ $paymentMethod }}</td>
        </tr>
@endif
@if($reference)
        <tr>
            <td style="padding:6px 0; color:#111827; font-weight:600;">Referencia</td>
            <td style="padding:6px 0; color:#475569; text-align:right;">{{ $reference }}</td>
        </tr>
@endif
@if($planName)
        <tr>
            <td style="padding:6px 0; color:#111827; font-weight:600;">Plan</td>
            <td style="padding:6px 0; color:#475569; text-align:right;">{{ $planName }}</td>
        </tr>
@endif
    </tbody>
</table>

<p style="margin:24px 0; text-align:center;">
    <a href="{{ $paymentsUrl }}"
       style="display:inline-block; background:{{ $colorDark }}; color:#ffffff; text-decoration:none; font-weight:600; padding:12px 20px; border-radius:8px;">
        Ver pagos
    </a>
</p>

Gracias por mantener tu cuenta al dia.

---

Si no funciona el boton "Ver pagos", copia y pega esta URL en tu navegador:

[{{ $paymentsUrl }}]({{ $paymentsUrl }})

@slot('footer')
    @component('mail::footer')
        <span style="display:block; margin-bottom:6px;">
            Descarga la app Android: <a href="{{ $androidAppUrl }}">{{ $androidAppUrl }}</a>
        </span>
        {{ date('Y') }} {{ $tenantName }}
    @endcomponent
@endslot
@endcomponent
