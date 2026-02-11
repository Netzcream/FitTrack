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
    $dueDate = $dueDate ?? null;
    $planName = $planName ?? null;
    $label = $label ?? null;
    $notes = $notes ?? null;
    $invoiceNumber = $invoiceNumber ?? null;
    $createdAt = $createdAt ?? null;
    $paymentsUrl = $paymentsUrl ?? '#';
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
        Generamos un nuevo invoice para tu cuenta en {{ $tenantName }}.
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
@if($dueDate)
        <tr>
            <td style="padding:6px 0; color:#111827; font-weight:600;">Vence</td>
            <td style="padding:6px 0; color:#475569; text-align:right;">{{ $dueDate }}</td>
        </tr>
@endif
@if($planName)
        <tr>
            <td style="padding:6px 0; color:#111827; font-weight:600;">Plan</td>
            <td style="padding:6px 0; color:#475569; text-align:right;">{{ $planName }}</td>
        </tr>
@endif
@if($label)
        <tr>
            <td style="padding:6px 0; color:#111827; font-weight:600;">Detalle</td>
            <td style="padding:6px 0; color:#475569; text-align:right;">{{ $label }}</td>
        </tr>
@endif
@if($notes)
        <tr>
            <td style="padding:6px 0; color:#111827; font-weight:600;">Notas</td>
            <td style="padding:6px 0; color:#475569; text-align:right;">{{ $notes }}</td>
        </tr>
@endif
@if($createdAt)
        <tr>
            <td style="padding:6px 0; color:#111827; font-weight:600;">Generado</td>
            <td style="padding:6px 0; color:#475569; text-align:right;">{{ $createdAt }}</td>
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

Si ya realizaste el pago, podes ignorar este aviso.

---

Si no funciona el boton "Ver pagos", copia y pega esta URL en tu navegador:

[{{ $paymentsUrl }}]({{ $paymentsUrl }})

@slot('footer')
    @component('mail::footer')
        {{ date('Y') }} {{ $tenantName }}
    @endcomponent
@endslot
@endcomponent
