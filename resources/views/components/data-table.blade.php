@props([
    'pagination' => null,
    'filters' => null,
    'head' => null,
    'modal' => null,
])

<div>
    {{-- Filtros arriba --}}
    @isset($filters)
        <div class="mb-4 flex flex-wrap gap-2 items-end w-full">
            {{ $filters }}
        </div>
    @endisset

    {{-- Tabla --}}
    <div class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="overflow-hidden border border-gray-200 dark:border-neutral-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        @isset($head)
                            <thead>
                                <tr>
                                    {{ $head }}
                                </tr>
                            </thead>
                        @endisset

                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            {{ $slot }}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Paginación --}}
        @if ($pagination)
            <div class="mt-4">
                {{ $pagination->links('components.preline.pagination') }}
            </div>
        @endif

        @isset($modal)
            {{ $modal }}
        @endisset
    </div>
</div>
