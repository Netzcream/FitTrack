<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        @php
            $baseColor = $colorBase ?? tenant_config('color_base', '#263d83');
            $darkColor = $colorDark ?? tenant_config('color_dark', '#1e2a5e');
        @endphp

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px 30px;
        }

        h1,
        h2,
        h3 {
            margin: 0;
        }

        h1 {
            color: {{ $baseColor }};
            margin-bottom: 4px;
            font-size: 20px;
        }

        h3 {
            color: #222;
            margin-bottom: 2px;
            font-size: 11px;
            font-weight: bold;
        }

        p {
            margin: 2px 0;
        }

        .meta {
            font-size: 10px;
            color: #555;
            line-height: 1.3;
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
            border-top: 3px solid {{ $baseColor }};
            margin: 8px 0 14px 0;
        }

        /* === CONTENIDO PRINCIPAL === */
        .day-block {
            margin-top: 15px;
            margin-bottom: 12px;
            page-break-before: auto;
        }

        .day-title {
            background-color: {{ $baseColor }};
            color: #fff;
            padding: 6px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .exercise {
            border: 1px solid #999;
            border-left: 4px solid {{ $baseColor }};
            border-radius: 3px;
            margin-bottom: 8px;
            padding: 6px 8px;
            page-break-inside: avoid;
            background-color: #fafafa;
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
            margin-top: 4px;
        }

        /* celdas de imágenes con espacio entre sí */
        .exercise-images-table td {
            padding-right: 4px;
            padding-bottom: 2px;
        }

        /* IMG estable dentro de celda */
        .exercise-images-table img {
            display: block;
            background: #fff;
            border-radius: 4px;
            border: 1px solid {{ $baseColor }};
            padding: 2px;
        }

        .notes {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
            padding: 3px 5px;
            background-color: #fff8dc;
            border-left: 2px solid #ffc107;
            border-radius: 2px;
        }

        table.meta-info {
            width: 100%;
            margin-top: 8px;
            margin-bottom: 4px;
            border-collapse: collapse;
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 6px;
        }

        table.meta-info td {
            padding: 5px 8px;
            font-size: 12px;
        }

        table.meta-info strong {
            color: {{ $darkColor }};
        }

        footer {
            position: fixed;
            bottom: 15px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #e0e0e0;
            padding-top: 8px;
        }

        .progress-space {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 10px;
            font-size: 9px;
            color: #666;
            background-color: #f9f9f9;
        }

        .progress-space th {
            text-align: center;
            font-weight: bold;
            border: 1px solid #ddd;
            padding: 2px 4px;
            background-color: #e9ecef;
            color: {{ $darkColor }};
        }

        .progress-space td {
            padding: 2px 4px;
            height: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- === ENCABEZADO CON LOGO Y NOMBRE DEL PLAN === --}}
    @php
        $colorBase = $colorBase ?? tenant_config('color_base', '#263d83');
        $colorDark = $colorDark ?? tenant_config('color_dark', '#1e2a5e');
        $logoUrl = $logo ?? (tenant()->config?->getFirstMediaUrl('logo') ?: 'https://placehold.co/500x150?text=' . tenant()->name);
    @endphp

    <table class="header-table">
        <tr>
            <td width="25%" style="text-align:left;">
                @if(file_exists($logoUrl) || filter_var($logoUrl, FILTER_VALIDATE_URL))
                    <img src="{{ $logoUrl }}" alt="{{ tenant()->name ?? 'FitTrack' }} Logo"
                        style="max-height:70px; max-width:220px; object-fit:contain;" />
                @endif
            </td>
            <td width="75%" style="text-align:right;">
                <h1 style="color: {{ $colorBase }}; font-size:24px; margin:0; font-weight:700;">
                    {{ $assignment->name ?? $plan->name ?? 'Plan de Entrenamiento' }}
                </h1>
                @if (isset($assignment) && $assignment->plan && $assignment->plan->description)
                    <p style="margin:4px 0 0 0; font-size:13px; color:#555;">
                        {{ $assignment->plan->description }}
                    </p>
                @elseif (isset($plan) && $plan->description)
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
            <td colspan="2"><strong>Alumno:</strong> {{ $student->full_name }}</td>
        </tr>
        <tr>
            @php
                $goal = null;
                $duration = null;
                if (isset($assignment) && $assignment->plan) {
                    $goal = $assignment->plan->goal;
                    $duration = $assignment->plan->duration;
                } elseif (isset($plan)) {
                    $goal = $plan->goal;
                    $duration = $plan->duration;
                }
            @endphp
            <td><strong>Meta:</strong> {{ $goal ?? '-' }}</td>
            <td><strong>Duración:</strong> {{ $duration ?? '-' }}</td>
        </tr>
        @php
            $startsAt = null;
            $endsAt = null;
            if (isset($assignment)) {
                $startsAt = $assignment->starts_at;
                $endsAt = $assignment->ends_at;
            } elseif (isset($plan)) {
                $startsAt = $plan->assigned_from;
                $endsAt = $plan->assigned_until;
            }
        @endphp
        @if ($startsAt)
            <tr>
                <td><strong>Desde:</strong> {{ $startsAt->format('d/m/Y') }}</td>
                <td><strong>Hasta:</strong> {{ $endsAt ? $endsAt->format('d/m/Y') : '-' }}</td>
            </tr>
        @endif
    </table>

    {{-- === DÍAS Y EJERCICIOS === --}}
    @foreach ($grouped as $day => $exercises)
        <div class="day-block">
            <div class="day-title">Día {{ $day }}</div>

            @foreach ($exercises as $item)
                @php
                    // $item es un objeto con datos del snapshot + 'exercise' (modelo completo)
                    $exercise = $item->exercise ?? null;
                    $name = $item->name ?? ($exercise?->name ?? 'Ejercicio');
                    $category = $exercise?->category ?? 'Sin categoría';
                    $detail = $item->detail ?? '';
                    $notes = $item->notes ?? '';
                    $description = $exercise?->description ?? '';
                @endphp

                <div class="exercise">
                    {{-- Texto principal --}}
                    <table class="exercise-text-table">
                        <tr>
                            <td>
                                <h3>{{ $name }}</h3>
                                <p class="meta">
                                    {{ ucfirst($category) }}
                                    @if ($detail)
                                        - <strong>{{ $detail }}</strong>
                                    @endif
                                    @if ($description)
                                        <br><i>{{ $description }}</i>
                                    @endif
                                </p>
                                @if ($notes)
                                    <p class="notes"><strong>Nota:</strong> {{ $notes }}</p>
                                @endif
                            </td>
                        </tr>
                    </table>

                    {{-- Imágenes --}}
                    @if ($exercise && $exercise->hasMedia('images'))
                        <table class="exercise-images-table">
                            <tr>
                                @foreach ($exercise->getMedia('images') as $media)
                                    @php
                                        $imagePath = $media->getPath();
                                    @endphp
                                    @if (file_exists($imagePath))
                                        <td>
                                            <img src="{{ $imagePath }}" alt="Ejercicio {{ $name }}"
                                                width="70" height="70">
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        </table>
                    @endif
                </div>

                {{-- Registro de progreso del alumno (compacto) --}}
                @php
                    // Calcular cantidad de semanas entre las fechas del plan
                    $weekCount = 4; // default
                    if (isset($assignment)) {
                        $start = $assignment->starts_at;
                        $end = $assignment->ends_at;
                    } elseif (isset($plan)) {
                        $start = $plan->assigned_from;
                        $end = $plan->assigned_until;
                    }

                    if (isset($start) && isset($end) && $start && $end) {
                        $diffInDays = $start->diffInDays($end);
                        // Usar floor() para contar solo semanas completas
                        $weekCount = (int) floor($diffInDays / 7);
                        // Máximo 5 semanas, mínimo 1
                        $weekCount = min(max($weekCount, 1), 5);
                    }
                @endphp
                <table class="progress-space">
                    <thead>
                        <tr>
                            <th width="15%">Sem</th>
                            <th width="35%">Series x Reps</th>
                            <th width="25%">Peso</th>
                            <th width="25%">Notas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 1; $i <= $weekCount; $i++)
                            <tr>
                                <td>{{ $i }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
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
        - generado el {{ now()->format('d/m/Y H:i') }}
        @if (tenant()->name)
            para <strong>{{ tenant()->name }}</strong>
        @endif
    </footer>

</body>

</html>
