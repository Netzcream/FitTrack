@props(['name', 'label' => null, 'accept' => '*/*', 'uploadedUrls' => [], 'maxFiles' => 5])

@php $target = 'files'; @endphp

<div class="space-y-2">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-white">
            {{ $label }}
        </label>
    @endif

    <label for="{{ $inputId }}"
        class="py-2 px-3 inline-flex items-center gap-x-2 text-xs font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 cursor-pointer relative">
        <span wire:loading wire:target="{{ $target }}">
            <svg class="animate-spin shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4" />
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
        </span>
        <span wire:loading.remove wire:target="{{ $target }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="17 8 12 3 7 8" />
                <line x1="12" x2="12" y1="3" y2="15" />
            </svg>
        </span>
        {{ __('site.upload_files') }}
        <input id="{{ $inputId }}" type="file" wire:model="files" accept="{{ $accept }}" class="hidden"
            multiple />
    </label>

    <div class="mt-2 space-y-1">
        @foreach ($uploadedUrls as $index => $url)
            <div
                class="flex items-center justify-between px-3 py-1 rounded-lg border text-sm bg-gray-100 dark:bg-neutral-800 text-zinc-700 dark:text-zinc-300 shadow-xs border-zinc-200 dark:border-white/10">
                <div class="truncate">
                    <a href="{{ $url }}" target="_blank">{{ basename($url) }}</a>
                </div>
                <button type="button" wire:click="removeFile({{ $index }})"
                    class="text-red-600 hover:text-red-800 text-xs ml-3">
                    {{ __('site.delete') }}
                </button>
            </div>
        @endforeach

        @foreach ($files as $index => $file)
            @if (is_object($file) && method_exists($file, 'getClientOriginalName'))
                <div
                    class="flex items-center justify-between px-3 py-1 rounded-lg border text-sm bg-gray-100 dark:bg-neutral-800 text-zinc-700 dark:text-zinc-300 shadow-xs border-zinc-200 dark:border-white/10">
                    <div >
                        <div class="flex items-center gap-x-2">



                            <svg title="pendiente" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="shrink-0 size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <div class="truncate">
                                {{ $file->getClientOriginalName() }}
                            </div>
                        </div>

                    </div>
                    <button type="button" wire:click="removePreview({{ $index }})"
                        class="text-red-600 hover:text-red-800 text-xs ml-3 cursor-pointer">
                        {{ __('site.delete') }}
                    </button>
                </div>
            @endif
        @endforeach
    </div>
</div>
