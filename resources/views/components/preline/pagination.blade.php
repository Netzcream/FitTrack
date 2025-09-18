@php
    // Mantengo tu comportamiento de scroll
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? <<<JS
           (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
        JS
        : '';
@endphp

@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-y-2" role="navigation" aria-label="Pagination Navigation">
        {{-- Móvil: solo Prev / Next --}}
        <div class="flex sm:hidden items-center gap-x-1 w-full justify-between">
            {{-- Prev --}}
            @if ($paginator->onFirstPage())
                <button
                    type="button"
                    class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
                    aria-label="{{ __('pagination.previous') }}"
                    disabled
                >
                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>
                    <span>{{ __('pagination.previous') }}</span>
                </button>
            @else
                <button
                    type="button"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                    class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
                    aria-label="{{ __('pagination.previous') }}"
                >
                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>
                    <span>{{ __('pagination.previous') }}</span>
                </button>
            @endif

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <button
                    type="button"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                    class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
                    aria-label="{{ __('pagination.next') }}"
                >
                    <span>{{ __('pagination.next') }}</span>
                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </button>
            @else
                <button
                    type="button"
                    class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
                    aria-label="{{ __('pagination.next') }}"
                    disabled
                >
                    <span>{{ __('pagination.next') }}</span>
                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </button>
            @endif
        </div>

        {{-- Desktop: contador + páginas --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div class="text-sm text-gray-700 leading-5 dark:text-gray-400">
                {{-- Texto "Mostrando x a y de z" (opcional) --}}
                <span>{{ __('Mostrando') }}</span>
                <span class="font-medium">{{ $paginator->firstItem() }}</span>
                <span>{{ __('a') }}</span>
                <span class="font-medium">{{ $paginator->lastItem() }}</span>
                <span>{{ __('de') }}</span>
                <span class="font-medium">{{ $paginator->total() }}</span>
                <span>{{ __('resultados') }}</span>
            </div>

            <div>
                <div class="flex items-center gap-x-1" aria-label="Pagination">
                    {{-- Botón Prev --}}
                    @if ($paginator->onFirstPage())
                        <button type="button"
                                class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
                                aria-label="{{ __('pagination.previous') }}"
                                disabled>
                            <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg>
                            <span>{{ __('pagination.previous') }}</span>
                        </button>
                    @else
                        <button type="button"
                                wire:click="previousPage('{{ $paginator->getPageName() }}')"
                                x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                                class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
                                aria-label="{{ __('pagination.previous') }}">
                            <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg>
                            <span>{{ __('pagination.previous') }}</span>
                        </button>
                    @endif

                    {{-- Números de página --}}
                    <div class="flex items-center gap-x-1">
                        @foreach ($elements as $element)
                            {{-- Tres puntos --}}
                            @if (is_string($element))
                                <span
                                    class="min-h-9.5 min-w-9.5 flex justify-center items-center text-gray-800 py-2 px-3 text-sm rounded-lg dark:text-white"
                                >{{ $element }}</span>
                            @endif

                            {{-- Páginas --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                        @if ($page == $paginator->currentPage())
                                            <button type="button"
                                                class="min-h-9.5 min-w-9.5 flex justify-center items-center bg-gray-200 text-gray-800 py-2 px-3 text-sm rounded-lg focus:outline-hidden focus:bg-gray-300 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-600 dark:text-white dark:focus:bg-neutral-500"
                                                aria-current="page">
                                                {{ $page }}
                                            </button>
                                        @else
                                            <button type="button"
                                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                                x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                                class="min-h-9.5 min-w-9.5 flex justify-center items-center text-gray-800 hover:bg-gray-100 py-2 px-3 text-sm rounded-lg focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
                                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                                {{ $page }}
                                            </button>
                                        @endif
                                    </span>
                                @endforeach
                            @endif
                        @endforeach
                    </div>

                    {{-- Botón Next --}}
                    @if ($paginator->hasMorePages())
                        <button type="button"
                                wire:click="nextPage('{{ $paginator->getPageName() }}')"
                                x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                                class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
                                aria-label="{{ __('pagination.next') }}">
                            <span>{{ __('pagination.next') }}</span>
                            <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>
                    @else
                        <button type="button"
                                class="min-h-9.5 min-w-9.5 py-2 px-2.5 inline-flex justify-center items-center gap-x-1.5 text-sm rounded-lg text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-white/10 dark:focus:bg-white/10"
                                aria-label="{{ __('pagination.next') }}"
                                disabled>
                            <span>{{ __('pagination.next') }}</span>
                            <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </nav>
@endif
