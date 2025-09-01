@props([
    'name',
    'label' => null,
    'accept' => 'image/png,image/jpeg,image/jpg,image/webp',
    'preview' => null,
    'uploadedUrl' => null,
    'width' => '128',
    'height' => '96',
    'radius' => 'rounded-md', // e.g., 'rounded', 'rounded-lg', 'rounded-xl'
])

<div class="space-y-2">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-white">
            {{ $label }}
        </label>
    @endif

    <div class="flex flex-wrap items-center gap-3 sm:gap-5">
        {{-- Contenedor visual del archivo --}}
        <label for="{{ $name }}" class="group relative z-0 cursor-pointer"
            style="width: {{ $width }}px; height: {{ $height }}px;">
            {{-- Spinner mientras sube --}}
            <div wire:loading wire:target="{{ $name }}"
                class="absolute inset-0 {{ $radius }} bg-white/70 dark:bg-black/50 z-10 border border-gray-300 dark:border-neutral-700"
                style="position: absolute;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%)">
                    <svg class="animate-spin h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v1m6.364 1.636l-.707.707M20 12h-1M17.364 17.364l-.707-.707M12 20v-1M6.636 17.364l.707-.707M4 12h1M6.636 6.636l.707.707" />
                    </svg>
                </div>
            </div>


            {{-- Imagen subida o temporal --}}
            @if ($preview)
                <img src="{{ $preview->temporaryUrl() }}" wire:target="{{ $name }}"
                    class="w-full h-full object-cover object-center {{ $radius }} shadow  border border-gray-300 dark:border-neutral-700" />
            @elseif ($uploadedUrl)
                <img src="{{ $uploadedUrl }}" wire:target="{{ $name }}"
                    class="w-full h-full object-cover object-center {{ $radius }} shadow  border border-gray-300 dark:border-neutral-700" />
            @else
                {{-- Icono base, oculto si está cargando --}}
                <span wire:loading.remove wire:target="{{ $name }}"
                    class="flex justify-center items-center w-full h-full border-2 border-dotted border-gray-300 text-gray-400 cursor-pointer {{ $radius }} hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-600 dark:hover:bg-neutral-700/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </span>
            @endif
        </label>



        <div class="grow">
            <div class="flex items-center gap-x-2">
                {{-- Botón para seleccionar archivo --}}
                <label
                    class="py-2 px-3 inline-flex items-center gap-x-2 text-xs font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 cursor-pointer">
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" x2="12" y1="3" y2="15" />
                    </svg>
                    {{ __('site.upload_image') }}
                    <input id="{{ $name }}" type="file" wire:model="{{ $name }}"
                        accept="{{ $accept }}" class="hidden" />
                </label>

                {{-- Botón para eliminar preview (Livewire) --}}
                @if ($preview)
                    <button type="button" wire:click="removePreview('{{ $name }}')"
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-500 shadow hover:bg-gray-50 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800">
                        {{ __('site.delete') }}
                    </button>
                @elseif ($uploadedUrl)
                    <button type="button" wire:click="removeMedia('{{ $name }}')"
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-500 shadow hover:bg-gray-50 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800">
                        {{ __('site.delete') }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    @error($name)
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
