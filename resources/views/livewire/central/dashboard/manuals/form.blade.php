<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">

        {{-- Header sticky --}}
        <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
            <div class="flex items-center justify-between gap-4 max-w-3xl">
                <div>
                    <flux:heading size="xl" level="1">
                        {{ $edit_mode ? __('manuals.edit_title') : __('manuals.create_title') }}
                    </flux:heading>
                    <flux:subheading class="mb-6">
                        {{ __('manuals.index_subheading') }}
                    </flux:subheading>
                </div>
                <div class="flex items-center gap-3">
                    <flux:checkbox size="sm" :label="__('site.back_list')" wire:model.defer="back" />
                    <flux:button as="a" href="{{ route('central.dashboard.manuals.index') }}" variant="ghost" size="sm" wire:navigate>
                        {{ __('site.back') }}
                    </flux:button>
                    <flux:button wire:click="save" size="sm">
                        {{ $edit_mode ? __('common.update') : __('common.save') }}
                    </flux:button>
                </div>
            </div>
            <flux:separator variant="subtle" class="mt-2" />
        </div>

        {{-- Formulario --}}
        <form wire:submit.prevent="save" class="space-y-6" id="manual-form">
            <div class="max-w-3xl space-y-4 pt-2">

                {{-- Título y Categoría --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <flux:input
                            wire:model.defer="title"
                            :label="__('manuals.title')"
                            placeholder="{{ __('manuals.title_placeholder') }}"
                            maxlength="255"
                            autofocus
                        />
                    </div>
                    <div>
                        <flux:select wire:model.defer="category" :label="__('manuals.category')">
                            <option value="">{{ __('manuals.category_select') }}</option>
                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                {{-- Slug y Fecha de publicación --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <flux:input
                            wire:model.defer="slug"
                            label="Slug"
                            placeholder="{{ __('Se genera automáticamente desde el título') }}"
                            maxlength="255"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-neutral-400">
                            {{ __('Dejar vacío para generar automáticamente') }}
                        </p>
                    </div>
                    <div>
                        <flux:input
                            wire:model.defer="published_at"
                            :label="__('manuals.published_at')"
                            type="date"
                        />
                    </div>
                </div>

                {{-- Resumen --}}
                <div>
                    <flux:textarea
                        wire:model.defer="summary"
                        :label="__('manuals.summary')"
                        rows="3"
                        placeholder="{{ __('manuals.summary_placeholder') }}"
                        maxlength="500"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-neutral-400">
                        {{ __('Máximo 500 caracteres') }}
                    </p>
                </div>

                {{-- Contenido --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-800 dark:text-white mb-2">
                        {{ __('manuals.content') }}
                    </label>
                    <div wire:ignore>
                        <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
                        <style>
                            trix-editor {
                                min-height: 15rem;
                                max-height: 30rem;
                                overflow-y: auto;
                                padding: 0.75rem;
                            }

                            /* Dark mode styles */
                            .dark trix-editor {
                                background-color: rgb(24 24 27);
                                color: rgb(244 244 245);
                                border-color: rgb(63 63 70);
                            }

                            .dark trix-toolbar {
                                background-color: rgb(39 39 42);
                                border-color: rgb(63 63 70);
                            }

                            .dark trix-toolbar .trix-button-row {
                                background-color: rgb(39 39 42);
                            }

                            .dark trix-toolbar .trix-button-group {
                                background: transparent;
                                border-color: rgb(63 63 70) !important;
                            }

                            .dark trix-toolbar .trix-button-group:not(:first-child) {
                                border-left-color: rgb(63 63 70) !important;
                            }

                            .dark trix-toolbar .trix-button {
                                color: rgb(228 228 231);
                                border: none;
                                background: transparent;
                                filter: invert(1) hue-rotate(180deg);
                            }

                            .dark trix-toolbar .trix-button:hover {
                                background-color: rgba(255, 255, 255, 0.1);
                            }

                            .dark trix-toolbar .trix-button.trix-active {
                                background-color: rgba(255, 255, 255, 0.2);
                            }

                            .dark trix-toolbar .trix-button:disabled {
                                opacity: 0.3;
                            }

                            .dark trix-toolbar .trix-dialogs {
                                background-color: rgb(39 39 42);
                                border-color: rgb(63 63 70);
                            }

                            .dark trix-toolbar .trix-dialog {
                                background-color: rgb(39 39 42);
                            }

                            .dark trix-toolbar .trix-dialog__link-fields input {
                                background-color: rgb(24 24 27);
                                color: rgb(244 244 245);
                                border-color: rgb(63 63 70);
                            }

                            .dark trix-toolbar .trix-dialog__link-fields input:focus {
                                outline: none;
                                border-color: rgb(96 165 250);
                            }
                        </style>

                        <input
                            id="content-{{ $edit_mode ? $manual->uuid : 'new' }}"
                            type="hidden"
                            name="content"
                        >
                        <trix-editor
                            input="content-{{ $edit_mode ? $manual->uuid : 'new' }}"
                            class="trix-content border border-zinc-200 dark:border-zinc-700 rounded-lg"
                        ></trix-editor>

                        <script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
                        <script>
                            (function() {
                                const editorId = 'content-{{ $edit_mode ? $manual->uuid : "new" }}';
                                const trixEditor = document.querySelector('trix-editor[input="' + editorId + '"]');
                                const hiddenInput = document.getElementById(editorId);

                                if (trixEditor && hiddenInput) {
                                    // Initialize editor with content
                                    const initialContent = @json($content);
                                    trixEditor.addEventListener("trix-initialize", function() {
                                        if (initialContent) {
                                            trixEditor.editor.loadHTML(initialContent);
                                        }
                                    }, { once: true });

                                    // Update Livewire property on blur (when user clicks outside editor)
                                    trixEditor.addEventListener("trix-blur", function() {
                                        @this.set('content', hiddenInput.value, false);
                                    });
                                }
                            })();
                        </script>
                    </div>
                </div>

                {{-- Activo --}}
                <div>
                    <flux:checkbox wire:model.defer="is_active" :label="__('manuals.is_active')" />
                </div>

                {{-- Icono del manual --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-zinc-800 dark:text-white">
                        {{ __('Icono del manual') }}
                    </label>

                    {{-- Preview del icono actual (si existe en edit) --}}
                    @if ($edit_mode && $manual->hasMedia('icon') && !$icon)
                        <div class="flex items-center gap-4">
                            <img
                                src="{{ $manual->getFirstMediaUrl('icon') }}"
                                alt="Icono actual"
                                class="w-16 h-16 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700"
                            >
                            <flux:button
                                wire:click="removeIcon"
                                variant="ghost"
                                size="sm"
                                type="button"
                            >
                                {{ __('Eliminar icono') }}
                            </flux:button>
                        </div>
                    @endif

                    {{-- Preview del nuevo icono seleccionado --}}
                    @if ($icon)
                        @php
                            $iconData = [
                                'url' => null,
                                'name' => 'Archivo',
                                'size' => 0
                            ];

                            try {
                                if ($icon && file_exists($icon->getRealPath())) {
                                    $iconData['url'] = $icon->temporaryUrl();
                                    $iconData['name'] = $icon->getClientOriginalName();
                                    $iconData['size'] = $icon->getSize();
                                }
                            } catch (\Exception $e) {
                                // Archivo temporal no disponible
                            }
                        @endphp

                        <div class="flex items-center gap-4 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            @if ($iconData['url'])
                                <img
                                    src="{{ $iconData['url'] }}"
                                    alt="Preview"
                                    class="w-16 h-16 object-cover rounded-lg"
                                >
                            @else
                                <flux:icon.photo variant="outline" class="w-16 h-16 text-zinc-400" />
                            @endif

                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-800 dark:text-white">
                                    {{ $iconData['name'] }}
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ number_format($iconData['size'] / 1024, 2) }} KB
                                </p>
                            </div>
                            <flux:button
                                wire:click="clearIconPreview"
                                variant="ghost"
                                size="sm"
                                type="button"
                            >
                                {{ __('Quitar') }}
                            </flux:button>
                        </div>
                    @endif

                    {{-- Input para subir icono --}}
                    @if (!$icon)
                        <div>
                            <input
                                wire:key="icon-input-{{ $attachmentInputKey }}"
                                type="file"
                                wire:model="icon"
                                accept="image/*"
                                class="block w-full text-sm text-zinc-500 dark:text-zinc-400
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-zinc-100 file:text-zinc-700
                                    dark:file:bg-zinc-800 dark:file:text-zinc-200
                                    hover:file:bg-zinc-200 dark:hover:file:bg-zinc-700
                                    cursor-pointer"
                            >
                            @error('icon')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <p class="text-xs text-gray-500 dark:text-neutral-400">
                        {{ __('Formatos aceptados: JPG, PNG, WebP, SVG. Máximo 2MB') }}
                    </p>
                </div>

                {{-- Archivos adjuntos --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-zinc-800 dark:text-white">
                        {{ __('Archivos adjuntos') }}
                    </label>

                    {{-- Archivos ya guardados --}}
                    @if ($edit_mode && $manual->hasMedia('attachments'))
                        <div class="space-y-2 mb-4">
                            <p class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Archivos guardados:</p>
                            @foreach ($manual->getMedia('attachments') as $media)
                                @if (!in_array($media->id, $attachmentsToDelete))
                                    <div wire:key="saved-{{ $media->id }}" class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                        <div class="flex items-center gap-3">
                                            <flux:icon.document-text variant="outline" class="w-5 h-5 text-zinc-500" />
                                            <div>
                                                <p class="text-sm font-medium text-zinc-800 dark:text-white">
                                                    {{ $media->file_name }}
                                                </p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ $media->human_readable_size }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <flux:button
                                                wire:key="download-{{ $media->id }}"
                                                as="a"
                                                href="{{ $media->getUrl() }}"
                                                target="_blank"
                                                variant="ghost"
                                                size="sm"
                                            >
                                                {{ __('Descargar') }}
                                            </flux:button>
                                            <flux:button
                                                wire:key="delete-{{ $media->id }}"
                                                wire:click="deleteAttachment({{ $media->id }})"
                                                variant="ghost"
                                                size="sm"
                                                type="button"
                                            >
                                                {{ __('Eliminar') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Archivos pendientes de guardar --}}
                    @if (count($pendingAttachments) > 0)
                        <div class="space-y-2 mb-4">
                            <p class="text-xs font-medium text-amber-600 dark:text-amber-400">
                                Archivos pendientes de guardar ({{ count($pendingAttachments) }}):
                            </p>
                            @foreach ($pendingAttachments as $index => $file)
                                @php
                                    $fileData = [
                                        'previewUrl' => null,
                                        'isImage' => false,
                                        'name' => 'Archivo',
                                        'size' => 0,
                                        'valid' => false
                                    ];

                                    try {
                                        if ($file && file_exists($file->getRealPath())) {
                                            $fileData['valid'] = true;
                                            $fileData['name'] = $file->getClientOriginalName();
                                            $fileData['size'] = $file->getSize();

                                            $mimeType = $file->getMimeType();
                                            if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
                                                $fileData['isImage'] = true;
                                                $fileData['previewUrl'] = $file->temporaryUrl();
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        // Archivo temporal no disponible
                                        $fileData['valid'] = false;
                                    }
                                @endphp

                                @if ($fileData['valid'])
                                    <div wire:key="pending-{{ $index }}" class="flex items-center justify-between p-3 bg-amber-50 dark:bg-amber-950/20 rounded-lg border border-amber-200 dark:border-amber-800">
                                        <div class="flex items-center gap-3">
                                            @if ($fileData['isImage'] && $fileData['previewUrl'])
                                                <img
                                                    src="{{ $fileData['previewUrl'] }}"
                                                    alt="Preview"
                                                    class="w-10 h-10 object-cover rounded"
                                                >
                                            @else
                                                <flux:icon.document-text variant="outline" class="w-5 h-5 text-amber-600" />
                                            @endif

                                            <div>
                                                <p class="text-sm font-medium text-zinc-800 dark:text-white">
                                                    {{ $fileData['name'] }}
                                                </p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ number_format($fileData['size'] / 1024, 2) }} KB
                                                </p>
                                            </div>
                                        </div>
                                        <flux:button
                                            wire:key="remove-pending-{{ $index }}"
                                            wire:click="removePendingAttachment({{ $index }})"
                                            variant="ghost"
                                            size="sm"
                                            type="button"
                                        >
                                            {{ __('Quitar') }}
                                        </flux:button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Input para subir más archivos --}}
                    <div>
                        <input
                            wire:key="attachments-input-{{ $attachmentInputKey }}"
                            type="file"
                            wire:model="newAttachments"
                            multiple
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,image/*"
                            class="block w-full text-sm text-zinc-500 dark:text-zinc-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-zinc-100 file:text-zinc-700
                                dark:file:bg-zinc-800 dark:file:text-zinc-200
                                hover:file:bg-zinc-200 dark:hover:file:bg-zinc-700
                                cursor-pointer"
                        >
                        @error('newAttachments.*')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <div wire:loading wire:target="newAttachments" class="mt-2">
                            <p class="text-xs text-blue-600 dark:text-blue-400">
                                Procesando archivos...
                            </p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-neutral-400">
                        {{ __('Formatos aceptados: PDF, Word, Excel, TXT, imágenes. Máximo 10MB por archivo. Podés subir múltiples tandas de archivos.') }}
                    </p>
                </div>

                {{-- Footer compacto --}}
                <div class="pt-6 max-w-3xl">
                    <div class="flex justify-end gap-3 items-center opacity-80">
                        <flux:checkbox size="sm" :label="__('site.back_list')" wire:model.defer="back" />
                        <flux:button as="a" href="{{ route('central.dashboard.manuals.index') }}" variant="ghost" size="sm" wire:navigate>
                            {{ __('site.back') }}
                        </flux:button>
                        <flux:button wire:click="save" size="sm">
                            {{ $edit_mode ? __('common.update') : __('common.save') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
