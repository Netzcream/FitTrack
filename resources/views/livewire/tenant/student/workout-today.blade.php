<div class="space-y-6 md:space-y-8" x-data="{
    accumulatedSeconds: 0,
    sessionStart: null,
    elapsedMinutes: 0,
    elapsedSeconds: 0,
    timer: null,
    lastPersistMinute: -1,
    workoutId: '{{ $workout?->uuid ?? $workout?->id ?? '' }}',
    manualOverride: false,
    allExercisesCompleted: false,
    exercisesData: @js($exercisesData),
    checkCompletion() {
        const total = this.exercisesData.length;
        const completed = this.exercisesData.filter(e => e.completed).length;
        this.allExercisesCompleted = (total > 0 && completed === total);
        if (this.allExercisesCompleted) {
            this.stopTimer();
        }
    },
    startTimer() {
        if (this.timer || this.allExercisesCompleted || this.manualOverride) return;
        this.sessionStart = Date.now();
        const update = () => {
            if (document.hidden || this.allExercisesCompleted || this.manualOverride) return;
            const sessionElapsed = Math.floor((Date.now() - this.sessionStart) / 1000);
            const totalSeconds = this.accumulatedSeconds + sessionElapsed;
            this.elapsedMinutes = Math.floor(totalSeconds / 60);
            this.elapsedSeconds = totalSeconds % 60;
            if (!this.manualOverride) {
                $wire.durationMinutes = this.elapsedMinutes;
            }
            if (this.elapsedMinutes !== this.lastPersistMinute) {
                this.lastPersistMinute = this.elapsedMinutes;
                $wire.persistLiveProgress(this.elapsedMinutes);
            }
        };
        update();
        this.timer = setInterval(update, 1000);
    },
    stopTimer() {
        if (this.timer) {
            if (this.sessionStart) {
                this.accumulatedSeconds += Math.floor((Date.now() - this.sessionStart) / 1000);
                localStorage.setItem('workout_' + this.workoutId + '_seconds', this.accumulatedSeconds);
            }
            clearInterval(this.timer);
            this.timer = null;
            this.sessionStart = null;
        }
    },
    init() {
        if (!this.workoutId) {
            return;
        }
        const saved = localStorage.getItem('workout_' + this.workoutId + '_seconds');
        this.accumulatedSeconds = saved ? parseInt(saved) : 0;
        this.checkCompletion();
        if (!document.hidden && !this.allExercisesCompleted) this.startTimer();
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) { this.stopTimer(); } else { if (!this.allExercisesCompleted) this.startTimer(); }
        });
        window.addEventListener('beforeunload', () => { this.stopTimer(); });
        Livewire.hook('morph.updated', () => {
            this.exercisesData = @this.exercisesData;
            this.checkCompletion();
        });
    }
}">
    {{-- ENCABEZADO --}}
    <x-student-header
        title="Entrenamiento de Hoy"
        subtitle="Completa todos los ejercicios de tu sesión"
        icon="zap"
        :student="$student" />

    @if (!$workout)
        <div class="bg-white rounded-xl shadow-md p-8 text-center border border-gray-200">
            <x-icons.lucide.alert-circle class="w-12 h-12 mx-auto text-gray-400 mb-3" />
            <p class="text-gray-700 font-medium mb-4">No hay entrenamiento activo</p>
            <a href="{{ route('tenant.student.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white" style="background-color: var(--ftt-color-base);">
                <x-icons.lucide.arrow-left class="w-4 h-4" />
                Volver al dashboard
            </a>
        </div>
    @else
        {{-- BARRA DE PROGRESO --}}
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-900">Progreso del Entrenamiento</h3>
                <span class="text-sm text-gray-500">
                    {{ count(array_filter($exercisesData, fn($e) => $e['completed'] ?? false)) }} de {{ count($exercisesData) }}
                </span>
            </div>
            <div class="bg-gray-100 h-3 rounded-full overflow-hidden">
                <div class="h-3 rounded-full transition-all duration-500"
                    style="width: {{ count($exercisesData) > 0 ? (count(array_filter($exercisesData, fn($e) => $e['completed'] ?? false)) / count($exercisesData) * 100) : 0 }}%; background-color: var(--ftt-color-base)">
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                Día {{ $workout->plan_day }} del plan • Ciclo {{ $workout->cycle_index }}
            </p>
        </div>

        {{-- EJERCICIOS --}}
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Ejercicios de Hoy</h3>

            @forelse ($exercisesData as $index => $exercise)
                @php
                    $images = array_values($exercise['images'] ?? []);
                    $mainImage = $exercise['image_url'] ?? ($images[0]['url'] ?? null);
                    $galleryUrls = array_values(array_filter(array_map(fn($img) => $img['url'] ?? null, $images)));
                @endphp

                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200" x-data="{ galleryOpen: false, galleryIndex: 0, images: @js($galleryUrls) }">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-3 mb-1">
                                <div class="flex items-center gap-2">
                                    <button wire:click="toggleExerciseComplete({{ $index }})" type="button"
                                            class="flex-shrink-0 transition hover:scale-110"
                                            style="color: {{ ($exercise['completed'] ?? false) ? 'var(--ftt-color-base)' : '#bfdbfe' }};"
                                            title="{{ ($exercise['completed'] ?? false) ? 'Marcar como incompleto' : 'Marcar como completado' }}">
                                        @if ($exercise['completed'] ?? false)
                                            <x-icons.lucide.badge-check class="w-6 h-6" />
                                        @else
                                            <x-icons.lucide.badge-info class="w-6 h-6" />
                                        @endif
                                    </button>
                                    <h4 class="font-bold text-gray-900 text-2xl">{{ $exercise['name'] ?? 'Ejercicio ' . ($index + 1) }}</h4>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($exercise['completed'] ?? false)
                                        <button wire:click="toggleExerciseComplete({{ $index }})" type="button"
                                                class="text-xs text-gray-600 underline">Desmarcar</button>
                                    @else
                                        <button wire:click="toggleExerciseComplete({{ $index }})" type="button"
                                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white hover:opacity-90 transition shadow-sm"
                                                style="background-color: var(--ftt-color-base)"
                                                aria-pressed="false">
                                            <x-icons.lucide.check class="w-5 h-5" /> Marcar como realizado
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3 mb-3">
                                @if (isset($exercise['detail']))
                                    <p class="text-lg text-gray-900">{{ $exercise['detail'] }}</p>
                                @endif
                                @if (isset($exercise['detail']) && isset($exercise['notes']) && $exercise['notes'])
                                    <span class="text-gray-400">|</span>
                                @endif
                                @if (isset($exercise['notes']) && $exercise['notes'])
                                    <p class="text-lg text-gray-900">{{ $exercise['notes'] }}</p>
                                @endif
                            </div>
                            @if (isset($exercise['notes']) && strpos($exercise['notes'], '💡') === 0)
                                <p class="text-sm text-blue-700 bg-blue-50 p-2 rounded mb-3">{{ $exercise['notes'] }}</p>
                            @endif
                        </div>
                    </div>

                    @if ($mainImage)
                        <div class="mb-4 flex flex-col md:flex-row gap-4 items-start">
                            <button type="button" class="w-full md:max-w-[55%] rounded-lg overflow-hidden bg-gray-100" style="aspect-ratio: 1 / 1; max-height: 420px;" @if(count($galleryUrls)) @click.prevent="galleryOpen = true; galleryIndex = 0" @endif>
                                <img src="{{ $mainImage }}" alt="{{ $exercise['name'] }}" class="w-full h-full object-contain">
                            </button>
                            @if (count($images) > 1)
                                @php
                                    // Show up to 6 thumbnails (skip the main image at index 0)
                                    $thumbLimit = 6;
                                    $startIndex = 1;
                                    $totalThumbs = max(count($images) - $startIndex, 0);

                                    // If we have more than 6 thumbnails, show 5 + overlay with extra count
                                    if ($totalThumbs > $thumbLimit) {
                                        $visibleThumbs = 5;
                                        $extraThumbs = $totalThumbs - 5;
                                    } else {
                                        $visibleThumbs = $totalThumbs;
                                        $extraThumbs = 0;
                                    }
                                @endphp
                                <div class="w-full md:max-w-[45%] md:h-[420px] grid grid-cols-2 md:grid-cols-2 md:grid-rows-3 gap-3 content-start self-stretch">
                                    @for ($i = 0; $i < $visibleThumbs; $i++)
                                        @php $imgIndex = $startIndex + $i; @endphp
                                        <button type="button" @click.prevent="galleryOpen = true; galleryIndex = {{ $imgIndex }}" class="block h-full">
                                            <img src="{{ $images[$imgIndex]['url'] ?? '' }}" alt="{{ $exercise['name'] }}" class="w-full h-full rounded-md object-cover border border-gray-200" loading="lazy">
                                        </button>
                                    @endfor

                                    @if ($extraThumbs > 0)
                                        @php $overlayIndex = $startIndex + 5; @endphp
                                        <button type="button" @click.prevent="galleryOpen = true; galleryIndex = {{ $overlayIndex }}" class="relative block h-full">
                                            <img src="{{ $images[$overlayIndex]['url'] ?? '' }}" alt="{{ $exercise['name'] }}" class="w-full h-full rounded-md object-cover border border-gray-200" loading="lazy">
                                            <span class="absolute inset-0 bg-black/60 text-white text-2xl font-bold flex items-center justify-center rounded-md">+{{ $extraThumbs }}</span>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- GALERÍA LIGHTBOX --}}
                    <div x-show="galleryOpen && images.length" x-cloak x-trap="galleryOpen" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-6"
                        @click.self="galleryOpen = false"
                        @keydown.escape.window="galleryOpen = false"
                        @keydown.left.window.prevent="galleryIndex = (galleryIndex + images.length - 1) % images.length"
                        @keydown.right.window.prevent="galleryIndex = (galleryIndex + 1) % images.length">
                        <button type="button" @click="galleryOpen = false" class="absolute top-6 right-6 text-white/80 hover:text-white">
                            <x-icons.lucide.x class="w-6 h-6" />
                        </button>

                        <div class="relative flex items-center justify-center w-full max-w-[90vw] max-h-[90vh]" @click.outside="galleryOpen = false">
                            <button type="button" @click.stop="galleryIndex = (galleryIndex + images.length - 1) % images.length" class="absolute left-3 md:left-6 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/70 text-gray-800 shadow hover:bg-white">
                                <x-icons.lucide.chevron-left class="w-5 h-5 m-auto" />
                            </button>

                            <img :src="images[galleryIndex]" class="max-h-[85vh] max-w-full object-contain rounded-md shadow-xl" />

                            <button type="button" @click.stop="galleryIndex = (galleryIndex + 1) % images.length" class="absolute right-3 md:right-6 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-white/70 text-gray-800 shadow hover:bg-white">
                                <x-icons.lucide.chevron-right class="w-5 h-5 m-auto" />
                            </button>
                        </div>
                    </div>

                    {{-- INFORMACIÓN DEL EJERCICIO --}}
                    <div class="mb-4 space-y-2 pb-4 border-b border-gray-200">
                        @if (isset($exercise['description']) && $exercise['description'])
                            <div>
                                <h5 class="text-xs font-semibold text-gray-500 uppercase mb-1">Descripción</h5>
                                <p class="text-sm text-gray-700">{{ $exercise['description'] }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @if (isset($exercise['category']))
                                <div class="text-xs">
                                    <span class="text-gray-500 font-medium">Grupo</span>
                                    <p class="text-gray-800">{{ $exercise['category'] }}</p>
                                </div>
                            @endif
                            @if (isset($exercise['level']))
                                <div class="text-xs">
                                    <span class="text-gray-500 font-medium">Nivel</span>
                                    <p class="text-gray-800">{{ $exercise['level'] }}</p>
                                </div>
                            @endif
                            @if (isset($exercise['equipment']))
                                <div class="text-xs">
                                    <span class="text-gray-500 font-medium">Equipo</span>
                                    <p class="text-gray-800">{{ $exercise['equipment'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- SERIES --}}
                    @if (isset($exercise['sets']) && is_array($exercise['sets']))
                        <div class="space-y-2">
                            @foreach ($exercise['sets'] as $setIndex => $set)
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm font-medium text-gray-600 w-8">Set {{ $setIndex + 1 }}</span>
                                    <div class="flex-1 flex items-center gap-2">
                                        @if (isset($set['reps']))
                                            <span class="text-sm text-gray-700">{{ $set['reps'] }} reps</span>
                                        @endif
                                        @if (isset($set['weight']))
                                            <span class="text-sm text-gray-700">× {{ $set['weight'] }} kg</span>
                                        @endif
                                        @if (isset($set['time']))
                                            <span class="text-sm text-gray-700">- {{ $set['time'] }}s</span>
                                        @endif
                                    </div>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"
                                            {{ ($set['completed'] ?? false) ? 'checked' : '' }}
                                            class="w-4 h-4 rounded"
                                            style="accent-color: var(--ftt-color-base)">
                                        <span class="text-sm text-gray-600">Realizado</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-md p-8 text-center border border-gray-200">
                    <p class="text-gray-600">No hay ejercicios para hoy</p>
                </div>
            @endforelse
        </div>

        {{-- ACCIONES FINALES --}}
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200 space-y-4">
            <h3 class="font-semibold text-gray-900">Finalizar Entrenamiento</h3>

            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duración (minutos)</label>
                    <input type="number" wire:model="durationMinutes" min="0" max="500"
                        @input="manualOverride = true"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        placeholder="Se calcula automáticamente">
                </div>

                <div class="w-32 mx-auto">
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-center">Tiempo transcurrido</label>
                    <div class="h-10 px-2 flex items-center justify-center font-mono text-lg text-gray-900"
                         x-text="String(elapsedMinutes).padStart(2, '0') + ':' + String(elapsedSeconds).padStart(2, '0')"></div>
                </div>

                <div x-data="{ rating: @entangle('rating') }">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Evaluación</label>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center">
                            <button type="button" class="p-1 cursor-pointer" @click="rating = (rating === 1 ? null : 1)" :aria-pressed="rating===1" aria-label="1 estrella">
                                <span x-bind:class="rating >= 1 ? 'text-yellow-500' : 'text-gray-300'">
                                    <x-icons.lucide.star class="w-6 h-6" />
                                </span>
                            </button>
                            <button type="button" class="p-1 cursor-pointer" @click="rating = (rating === 2 ? null : 2)" :aria-pressed="rating===2" aria-label="2 estrellas">
                                <span x-bind:class="rating >= 2 ? 'text-yellow-500' : 'text-gray-300'">
                                    <x-icons.lucide.star class="w-6 h-6" />
                                </span>
                            </button>
                            <button type="button" class="p-1 cursor-pointer" @click="rating = (rating === 3 ? null : 3)" :aria-pressed="rating===3" aria-label="3 estrellas">
                                <span x-bind:class="rating >= 3 ? 'text-yellow-500' : 'text-gray-300'">
                                    <x-icons.lucide.star class="w-6 h-6" />
                                </span>
                            </button>
                            <button type="button" class="p-1 cursor-pointer" @click="rating = (rating === 4 ? null : 4)" :aria-pressed="rating===4" aria-label="4 estrellas">
                                <span x-bind:class="rating >= 4 ? 'text-yellow-500' : 'text-gray-300'">
                                    <x-icons.lucide.star class="w-6 h-6" />
                                </span>
                            </button>
                            <button type="button" class="p-1 cursor-pointer" @click="rating = (rating === 5 ? null : 5)" :aria-pressed="rating===5" aria-label="5 estrellas">
                                <span x-bind:class="rating >= 5 ? 'text-yellow-500' : 'text-gray-300'">
                                    <x-icons.lucide.star class="w-6 h-6" />
                                </span>
                            </button>
                        </div>
                        <span class="text-sm text-gray-600" x-text="rating ? ['No me gustó','Podría ser mejor','Normal','Me gustó','Me encantó'][rating-1] : 'Sin calificar'"></span>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notas (opcional)</label>
                <textarea wire:model="notes" rows="3" placeholder="¿Cómo te sentiste? ¿Algo que destacar o mejorar? Te leo."
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                    <x-icons.lucide.scale class="w-4 h-4" />
                    Peso actual (kg) - Opcional
                </label>
                <input type="number" wire:model="currentWeight" step="0.1" min="20" max="300" placeholder="Ej: 75.5"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                    <x-icons.lucide.lightbulb class="w-3 h-3" />
                    Ayudanos a trackear tu progreso ingresando tu peso actual
                </p>
            </div>

            {{-- Encuesta rápida --}}
            <div class="space-y-3 pt-3 border-t border-gray-200">
                <h4 class="text-sm font-medium text-gray-900">Encuesta Rápida</h4>

                <div class="grid grid-cols-1 gap-4">
                    <div x-data="{ effort: @entangle('survey.effort').live }">
                        <label class="block text-xs font-medium text-gray-700 mb-2">Esfuerzo percibido (1-10)</label>
                        <input type="range" min="1" max="10" x-model.number="effort" @change="$wire.persistLiveProgress($wire.durationMinutes, effort)"
                            class="w-full" style="accent-color: var(--ftt-color-base)">
                        <span class="text-xs text-gray-500" x-text="(effort ?? 0) + '/10'"></span>
                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex gap-3 pt-3 border-t border-gray-200">
                <button wire:click="completeWorkout"
                    class="flex-1 px-4 py-3 rounded-lg text-sm font-semibold text-white transition flex items-center justify-center gap-2"
                    style="background-color: var(--ftt-color-base);">
                    <x-icons.lucide.check-circle class="w-5 h-5" />
                    Completar Entrenamiento
                </button>

                <button wire:click="skipWorkout"
                    class="flex-1 px-4 py-3 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition flex items-center justify-center gap-2">
                    <x-icons.lucide.skip-forward class="w-5 h-5" />
                    Saltar
                </button>
            </div>
        </div>

        {{-- Volver --}}
        <div class="text-center">
            <a href="{{ route('tenant.student.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Volver al dashboard
            </a>
        </div>
    @endif
</div>
