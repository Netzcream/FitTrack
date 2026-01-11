<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">

        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <flux:button
                    as="a"
                    href="{{ route('tenant.dashboard.manuals.index') }}"
                    variant="ghost"
                    size="sm"
                    icon="arrow-left"
                    wire:navigate
                >
                    {{ __('Volver a manuales') }}
                </flux:button>
            </div>

            {{-- Categoría --}}
            <div class="mb-3">
                <flux:badge size="sm" color="zinc">
                    <x-dynamic-component :component="'icons.lucide.' . $manual->category->icon()" class="w-3.5 h-3.5 me-1" />
                    {{ $manual->category->label() }}
                </flux:badge>
            </div>

            {{-- Título --}}
            <flux:heading size="xl" level="1" class="mb-2">
                {{ $manual->title }}
            </flux:heading>

            {{-- Metadata --}}
            <div class="flex items-center gap-4 text-sm text-zinc-600 dark:text-zinc-400">
                <div class="flex items-center gap-2">
                    <flux:icon.calendar variant="micro" />
                    <span>{{ __('Publicado el') }} {{ $manual->published_at->format('d/m/Y') }}</span>
                </div>
                @if ($manual->hasMedia('attachments'))
                    <div class="flex items-center gap-2">
                        <flux:icon.paper-clip variant="micro" />
                        <span>{{ $manual->getMedia('attachments')->count() }} {{ __('archivos adjuntos') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <flux:separator variant="subtle" class="mb-6" />

        {{-- Icono destacado --}}
        @if ($manual->hasMedia('icon'))
            <div class="mb-6">
                <img
                    src="{{ $manual->getFirstMediaUrl('icon') }}"
                    alt="{{ $manual->title }}"
                    class="w-full max-w-2xl h-auto object-cover rounded-lg"
                >
            </div>
        @endif

        {{-- Resumen --}}
        @if ($manual->summary)
            <div class="mb-6">
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-5">
                    <flux:heading size="lg" class="mb-3">
                        {{ __('Resumen') }}
                    </flux:heading>
                    <p class="text-zinc-700 dark:text-zinc-300 leading-relaxed">
                        {{ $manual->summary }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Contenido principal --}}
        <div class="mb-8">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-6 prose prose-zinc dark:prose-invert max-w-none">
                {!! $manual->content !!}
            </div>
        </div>

        {{-- Archivos adjuntos --}}
        @if ($manual->hasMedia('attachments'))
            <div class="mb-8">
                <flux:heading size="lg" class="mb-4">
                    {{ __('Archivos adjuntos') }}
                </flux:heading>

                <div class="space-y-3">
                    @foreach ($manual->getMedia('attachments') as $media)
                        <div wire:key="attachment-{{ $media->id }}" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    @if (str_starts_with($media->mime_type, 'image/'))
                                        <flux:icon.photo variant="outline" class="w-8 h-8 text-blue-600" />
                                    @elseif (str_contains($media->mime_type, 'pdf'))
                                        <flux:icon.document-text variant="outline" class="w-8 h-8 text-red-600" />
                                    @elseif (str_contains($media->mime_type, 'word'))
                                        <flux:icon.document-text variant="outline" class="w-8 h-8 text-blue-600" />
                                    @elseif (str_contains($media->mime_type, 'excel') || str_contains($media->mime_type, 'spreadsheet'))
                                        <flux:icon.document-text variant="outline" class="w-8 h-8 text-green-600" />
                                    @else
                                        <flux:icon.document variant="outline" class="w-8 h-8 text-zinc-600" />
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-800 dark:text-white">
                                        {{ $media->file_name }}
                                    </p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $media->human_readable_size }}
                                    </p>
                                </div>
                            </div>
                            <flux:button
                                as="a"
                                href="{{ $media->getUrl() }}"
                                target="_blank"
                                download
                                variant="primary"
                                size="sm"
                                icon="arrow-down-tray"
                            >
                                {{ __('Descargar') }}
                            </flux:button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Footer con botón de regreso --}}
        <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-700">
            <flux:button
                as="a"
                href="{{ route('tenant.dashboard.manuals.index') }}"
                variant="ghost"
                icon="arrow-left"
                wire:navigate
            >
                {{ __('Volver a manuales') }}
            </flux:button>
        </div>
    </div>
</div>
