@props(['name', 'url' => null, 'remove' => null, 'compact' => false])

@php
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    // Mapa extensión -> nombre de ícono en Lucide
    // Asegurate de generar estos componentes (file, file-text, file-archive, image, file-audio-2, file-video-2, file-code-2, file-spreadsheet).
    $iconMap = [
        // documentos
        'pdf' => 'file-text',
        'doc' => 'file-text',
        'docx' => 'file-text',
        'odt' => 'file-text',
        'rtf' => 'file-text',
        'txt' => 'file-text',
        'log' => 'file-text',

        // planillas
        'xls' => 'file-spreadsheet',
        'xlsx' => 'file-spreadsheet',
        'ods' => 'file-spreadsheet',
        'csv' => 'file-spreadsheet',

        // imágenes
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image',
        'webp' => 'image',
        'bmp' => 'image',
        'tiff' => 'image',
        'svg' => 'image',

        // comprimidos
        'zip' => 'file-archive',
        'rar' => 'file-archive',
        '7z' => 'file-archive',
        'tar' => 'file-archive',
        'gz' => 'file-archive',

        // audio / video
        'mp3' => 'file-audio-2',
        'wav' => 'file-audio-2',
        'ogg' => 'file-audio-2',
        'm4a' => 'file-audio-2',
        'flac' => 'file-audio-2',
        'mp4' => 'file-video-2',
        'mov' => 'file-video-2',
        'avi' => 'file-video-2',
        'mkv' => 'file-video-2',
        'webm' => 'file-video-2',

        // código
        'js' => 'file-code-2',
        'ts' => 'file-code-2',
        'php' => 'file-code-2',
        'css' => 'file-code-2',
        'html' => 'file-code-2',
        'json' => 'file-code-2',
        'xml' => 'file-code-2',
        'yml' => 'file-code-2',
        'yaml' => 'file-code-2',
    ];

    $icon = $iconMap[$ext] ?? 'file';

    // Si el componente Lucide no existe, usar 'file' genérico
    $iconView = "components.icons.lucide.$icon";
    if (!view()->exists($iconView)) {
        $icon = 'file';
        $iconView = "components.icons.lucide.$icon";
    }
@endphp
@if ($compact)
    {{-- VERSIÓN COMPACTA: una línea, truncado, ícono chico --}}
    <div class="inline-flex items-center gap-1 max-w-[12rem]">
        <div class="shrink-0 text-zinc-600 dark:text-zinc-300 w-4 h-4 flex items-center justify-center">
            @includeIf($iconView, ['class' => 'w-4 h-4'])
        </div>
        @if ($url)
            <a href="{{ $url }}" target="_blank" class="truncate text-xs" title="{{ $name }}">
                {{ $name }}
            </a>
        @else
            <span class="truncate text-xs" title="{{ $name }}">{{ $name }}</span>
        @endif
    </div>
@else
    {{-- VERSIÓN GRANDE: tarjeta con 3 líneas de texto --}}
    <div
        class="flex flex-col items-center gap-2 p-3 rounded-lg border
               bg-gray-100 dark:bg-neutral-800 text-zinc-700 dark:text-zinc-200
               shadow-xs border-zinc-200 dark:border-white/10">

        <div class="w-10 h-10 flex items-center justify-center text-zinc-600 dark:text-zinc-300">
            @includeIf($iconView, ['class' => 'w-10 h-10'])
        </div>

        @if ($url)
            <a href="{{ $url }}" target="_blank"
                class="text-center text-xs leading-snug max-w-full line-clamp-3" title="{{ $name }}">
                {{ $name }}
            </a>
        @else
            <span class="text-center text-xs leading-snug max-w-full line-clamp-3" title="{{ $name }}">
                {{ $name }}
            </span>
        @endif

        @if ($remove)
            <flux:button size="xs" class="mt-auto" type="button" variant="ghost"
                wire:click="{{ $remove }}">
                {{ __('common.delete') }}
            </flux:button>
        @endif
    </div>
@endif
