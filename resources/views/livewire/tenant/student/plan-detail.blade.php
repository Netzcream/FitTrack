<div class="space-y-6 md:space-y-8">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <x-icons.lucide.dumbbell class="w-8 h-8" />
                Detalle del plan
            </h1>
            <p class="text-gray-500">Tu rutina completa por días y ejercicios</p>
        </div>
        <a href="{{ route('tenant.student.dashboard') }}" class="text-sm text-gray-600 underline">Volver al panel</a>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200 space-y-2">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <x-icons.lucide.book class="w-5 h-5" />
            <span>{{ $assignment->name }}</span>
            @if ($assignment->version_label)
                <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-semibold">{{ $assignment->version_label }}</span>
            @endif
        </div>
        <p class="text-sm text-gray-500">
            Vigente desde {{ $assignment->starts_at?->format('d/m/Y') ?? '—' }}
            @if ($assignment->ends_at)
                · hasta {{ $assignment->ends_at->format('d/m/Y') }}
            @endif
        </p>
        <div class="flex flex-wrap gap-3 text-sm text-gray-600">
            <span>{{ $assignment->exercises_by_day->count() }} días</span>
            <span>{{ $assignment->exercises_by_day->flatten(1)->count() }} ejercicios</span>
            <span>Objetivo: {{ $assignment->plan?->goal ?? '—' }}</span>
        </div>
        <div>
            <a href="{{ route('tenant.student.download-plan', $assignment->uuid) }}"
               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold text-white shadow-sm transition"
               style="background-color: var(--ftt-color-base);">
                <x-icons.lucide.file-down class="w-4 h-4" /> Descargar PDF
            </a>
        </div>
    </div>

    <div class="space-y-4">
        @forelse ($assignment->exercises_by_day as $day => $items)
            <div class="border border-gray-200 rounded-xl bg-gray-50" x-data="{ open: true }">
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                            <x-icons.lucide.list-ordered class="w-5 h-5 text-gray-600" />
                        <p class="font-semibold text-gray-800">Día {{ $day }}</p>
                        <span class="text-xs px-2 py-1 rounded-full bg-white border border-gray-200 text-gray-600">
                            {{ $items->count() }} ejercicios
                        </span>
                    </div>
                    <button @click="open = !open" class="text-xs text-gray-600 underline">
                        <span x-show="open">Ocultar</span>
                        <span x-show="!open">Ver</span>
                    </button>
                </div>

                <ul class="space-y-3 p-4 pt-0" x-show="open" x-collapse>
                    @foreach ($items as $exercise)
                        @php $info = $exerciseInfo[$exercise['exercise_id'] ?? 0] ?? []; @endphp
                        <li class="bg-white border border-gray-200 rounded-lg p-3"
                            x-data="{ detailsOpen: false, galleryOpen: false, galleryIndex: 0, images: @js($info['images'] ?? []) }"
                            @click="detailsOpen = !detailsOpen" role="button" tabindex="0"
                            @keydown.window="if (galleryOpen && images.length) { if ($event.key === 'ArrowRight') { galleryIndex = (galleryIndex + 1) % images.length; $event.preventDefault(); } else if ($event.key === 'ArrowLeft') { galleryIndex = (galleryIndex + images.length - 1) % images.length; $event.preventDefault(); } else if ($event.key === 'Escape') { galleryOpen = false; $event.preventDefault(); } }">
                            <div class="flex gap-3">
                                <button @click.stop="galleryOpen = true; galleryIndex = 0" class="flex-shrink-0" :disabled="images.length === 0">
                                    @if ($info && $info['thumb'])
                                        <img src="{{ $info['thumb'] }}" alt="{{ $info['name'] ?? 'Ejercicio' }}" class="w-20 rounded object-cover border" style="aspect-ratio: 4 / 3;" />
                                    @else
                                        <div class="w-20 rounded bg-gray-100 border flex items-center justify-center text-gray-400" style="aspect-ratio: 4 / 3;">
                                            <x-icons.lucide.image class="w-6 h-6" />
                                        </div>
                                    @endif
                                </button>

                                <div class="flex-1">
                                    <div class="flex items-start justify-between gap-3" @click.stop="detailsOpen = !detailsOpen">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $exercise['name'] ?? $info['name'] ?? 'Ejercicio' }}</p>
                                            <p class="text-xs text-gray-600">
                                                {{ $exercise['detail'] ?? '' }}
                                                @if (!empty($info['equipment']))
                                                    <span class="text-gray-400">• {{ $info['equipment'] }}</span>
                                                @endif
                                            </p>
                                            @if (!empty($exercise['notes']))
                                                <p class="text-xs text-gray-500 italic mt-1">{{ $exercise['notes'] }}</p>
                                            @endif
                                        </div>
                                        <button type="button" @click.stop.prevent="detailsOpen = !detailsOpen" class="text-xs px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-100" :aria-expanded="detailsOpen.toString()">
                                            <span x-text="detailsOpen ? 'Ocultar' : 'Ver detalles'">Ver detalles</span>
                                        </button>
                                    </div>

                                    <div class="mt-3 text-sm text-gray-700 space-y-2" x-show="detailsOpen" x-collapse>
                                        @if (!empty($info['category']) || !empty($info['level']))
                                            <p class="text-xs text-gray-500">
                                                {{ $info['category'] ?? '' }}
                                                @if (!empty($info['level']))
                                                    • Nivel: {{ $info['level'] }}
                                                @endif
                                            </p>
                                        @endif
                                        @if (!empty($info['description']))
                                            <p>{{ $info['description'] }}</p>
                                        @endif

                                        @if (!empty($info['images']))
                                            @php
                                                $thumbs = $info['images'] ?? [];
                                                $thumbLimit = 5;
                                                $totalThumbs = count($thumbs);
                                                $extra = max($totalThumbs - $thumbLimit, 0);
                                                $visibleCount = $extra ? $thumbLimit - 1 : $totalThumbs;
                                            @endphp
                                                <div class="grid grid-cols-5 gap-2">
                                                @for ($i = 0; $i < $visibleCount; $i++)
                                                        <button @click.stop="galleryOpen = true; galleryIndex = {{ $i }}" class="block">
                                                            <img src="{{ $thumbs[$i] }}" class="w-full rounded border object-cover" style="aspect-ratio: 4 / 3;" />
                                                    </button>
                                                @endfor

                                                @if ($extra > 0)
                                                    @php $overlayIndex = $visibleCount; @endphp
                                                        <button @click.stop="galleryOpen = true; galleryIndex = {{ $overlayIndex }}" class="relative block">
                                                        <img src="{{ $thumbs[$overlayIndex] }}" class="w-full rounded border object-cover" style="aspect-ratio: 4 / 3;" />
                                                        <span class="absolute inset-0 bg-black/50 text-white text-sm font-semibold flex items-center justify-center rounded">+{{ $extra }}</span>
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Per-exercise Gallery Overlay (full screen, dark background) -->
                            <div x-show="galleryOpen && images.length" x-cloak class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-6" @click.stop>
                                    <button @click.stop="galleryOpen = false" type="button"
                                    class="absolute top-6 right-6 text-white/80 hover:text-white">
                                    <x-icons.lucide.x class="w-6 h-6" />
                                </button>

                                <div class="relative flex items-center justify-center w-full max-w-[90vw] max-h-[90vh]">
                                        <button type="button"
                                            @click.stop="galleryIndex = (galleryIndex + images.length - 1) % images.length"
                                            class="absolute left-3 md:left-6 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/70 text-gray-800 shadow hover:bg-white">
                                        <x-icons.lucide.chevron-left class="w-5 h-5 m-auto" />
                                    </button>

                                    <img :src="images[galleryIndex]" class="max-h-[85vh] max-w-full object-contain rounded-md shadow-xl" />

                                    <button type="button"
                                        @click.stop="galleryIndex = (galleryIndex + 1) % images.length"
                                        class="absolute right-3 md:right-6 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/70 text-gray-800 shadow hover:bg-white">
                                        <x-icons.lucide.chevron-right class="w-5 h-5 m-auto" />
                                    </button>
                                </div>

                                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-xs text-white/80 tracking-wide"
                                    x-text="(galleryIndex + 1) + ' / ' + images.length"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <div class="text-sm text-gray-500">Tu entrenador aún no cargó ejercicios.</div>
        @endforelse
    </div>

    {{-- Modal Livewire eliminado en favor de acordeón + galería por ejercicio --}}
</div>
