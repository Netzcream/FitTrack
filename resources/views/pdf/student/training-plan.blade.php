<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 0;
            padding: 30px 40px;
        }

        h1,
        h2,
        h3 {
            margin: 0;
        }

        h1 {
            color: {{ tenant_config('color_base', '#263d83') }};
            margin-bottom: 5px;
        }

        h3 {
            color: #444;
            margin-bottom: 4px;
        }

        p {
            margin: 3px 0;
        }

        .meta {
            font-size: 12px;
            color: #555;
            line-height: 1.4;
        }

        /* === ENCABEZADO PROFESIONAL === */
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: bottom;
            padding: 0;
        }

        .header-divider {
            border: none;
            border-top: 2px solid {{ tenant_config('color_base', '#263d83') }};
            margin: 8px 0 14px 0;
        }

        /* === CONTENIDO PRINCIPAL === */
        .day-block {
            margin-top: 20px;
        }

        .day-title {
            background-color: {{ tenant_config('color_base', '#263d83') }};
            color: #fff;
            padding: 6px 10px;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .exercise {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 12px;
            padding: 8px 10px;
            page-break-inside: avoid;
        }

        /* tablas internas: DomPDF las respeta y evita solapamientos */
        .exercise-text-table,
        .exercise-images-table {
            width: 100%;
            border-collapse: collapse;
        }

        .exercise-text-table td,
        .exercise-images-table td {
            padding: 0;
            vertical-align: top;
        }

        /* separación suave entre texto e imágenes */
        .exercise-images-table {
            margin-top: 6px;
        }

        /* celdas de imágenes con espacio entre sí */
        .exercise-images-table td {
            padding-right: 6px;
            padding-bottom: 6px;
        }

        /* IMG estable dentro de celda */
        .exercise-images-table img {
            display: block;
            /* evita baseline extraño en DomPDF */
            background: #fff;
            border-radius: 6px;
            border: 1px solid #ddd;
            padding: 3px;
        }

        .notes {
            font-size: 12px;
            color: #555;
            margin-top: 4px;
        }

        table.meta-info {
            width: 100%;
            margin-top: 6px;
            border-collapse: collapse;
        }

        table.meta-info td {
            padding: 3px 0;
        }

        footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #888;
        }

        .progress-space {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 11px;
            color: #555;
        }

        .progress-space th {
            text-align: left;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            padding: 2px 4px;
        }

        .progress-space td {
            padding: 3px 4px;
            height: 16px;
        }
    </style>
</head>

<body>

    {{-- === ENCABEZADO CON LOGO Y NOMBRE DEL PLAN === --}}
    @php
        $logo = tenant()->config?->getFirstMediaUrl('logo') ?: 'https://placehold.co/500x150?text=' . tenant()->name;
        $colorBase = tenant_config('color_base', '#263d83');
    @endphp

    <table class="header-table">
        <tr>
            <td width="25%" style="text-align:left;">
                <img src="{{ $logo }}" alt="{{ tenant()->name ?? 'FitTrack' }} Logo"
                    style="max-height:70px; max-width:220px; object-fit:contain;" />
            </td>
            <td width="75%" style="text-align:right;">
                <h1 style="color: {{ $colorBase }}; font-size:24px; margin:0; font-weight:700;">
                    {{ $plan->name }}
                </h1>
                @if ($plan->description)
                    <p style="margin:4px 0 0 0; font-size:13px; color:#555;">
                        {{ $plan->description }}
                    </p>
                @endif
            </td>
        </tr>
    </table>

    <hr class="header-divider">

    {{-- Información del plan --}}
    <table class="meta-info">
        <tr>
            <td><strong>Alumno:</strong> {{ $student->full_name }}</td>
            <td><strong>Versión:</strong> {{ $plan->version_label }}</td>
        </tr>
        <tr>
            <td><strong>Meta:</strong> {{ $plan->goal ?? '—' }}</td>
            <td><strong>Duración:</strong> {{ $plan->duration ?? '—' }}</td>
        </tr>
        @if ($plan->assigned_from)
            <tr>
                <td><strong>Desde:</strong> {{ $plan->assigned_from?->format('d/m/Y') }}</td>
                <td><strong>Hasta:</strong> {{ $plan->assigned_until?->format('d/m/Y') }}</td>
            </tr>
        @endif
    </table>

    {{-- === DÍAS Y EJERCICIOS === --}}
    @foreach ($grouped as $day => $exercises)
        <div class="day-block">
            <div class="day-title">Día {{ $day }}</div>

            @foreach ($exercises as $ex)
                <div class="exercise">
                    {{-- Texto principal --}}
                    <table class="exercise-text-table">
                        <tr>
                            <td>
                                <h3>{{ $ex->name }}</h3>
                                <p class="meta">
                                    {{ ucfirst($ex->category ?? 'Sin categoría') }}
                                    @if ($ex->pivot->detail)
                                        — <strong>{{ $ex->pivot->detail }}</strong>
                                    @endif
                                    @if ($ex->description)
                                        <br><i>{{ $ex->description }}</i>
                                    @endif
                                </p>
                                @if ($ex->pivot->notes)
                                    <p class="notes"><strong>Nota:</strong> {{ $ex->pivot->notes }}</p>
                                @endif
                            </td>
                        </tr>
                    </table>

                    {{-- Imágenes --}}
                    @if ($ex->hasMedia('images'))
                        <table class="exercise-images-table">
                            <tr>
                                @foreach ($ex->getMedia('images') as $media)
                                    <td>
                                        <img src="{{ $media->getPath() }}" alt="Ejercicio {{ $ex->name }}"
                                            width="110" height="110">
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                    @endif
                </div>

                {{-- Registro de progreso del alumno --}}
                <table class="progress-space">
                    <thead>
                        <tr style="background-color:#f9f9f9;">
                            <th>Semana</th>
                            <th>Reps / Tiempo / Distancia</th>
                            <th>Peso / Carga</th>
                            <th>Comentarios</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 1; $i <= 4; $i++)
                            <tr>
                                <td>{{ $i }}</td>
                                <td style="border-bottom:1px dotted #bbb;"></td>
                                <td style="border-bottom:1px dotted #bbb;"></td>
                                <td style="border-bottom:1px dotted #bbb;"></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            @endforeach
        </div>
    @endforeach

    {{-- Pie de página --}}
    <footer>
        <span style="color: {{ $colorBase }}; font-weight:600;">FitTrack</span>
        — generado el {{ now()->format('d/m/Y H:i') }}
        @if (tenant()->name)
            para <strong>{{ tenant()->name }}</strong>
        @endif
    </footer>

</body>

</html>
