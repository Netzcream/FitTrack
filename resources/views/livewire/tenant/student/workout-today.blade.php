<div class="space-y-6">
    <h2 class="text-2xl font-semibold text-green-700">
        💪 Entrenamiento de hoy
    </h2>

    @if (!$session)
        <div class="bg-white p-6 rounded-xl shadow text-gray-600">
            No tenés ningún entrenamiento pendiente. ¡Disfrutá tu día libre! 😎
        </div>
    @else
        <div class="bg-white p-6 rounded-xl shadow space-y-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-lg font-semibold text-gray-800">
                        {{ $session->planWorkout?->plan?->name ?? 'Plan sin nombre' }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Semana {{ $session->planWorkout?->week_index ?? '?' }} • Día
                        {{ $session->planWorkout?->day_index ?? '?' }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">
                        Estado:
                        <span
                            class="font-medium text-{{ $session->status === 'completed' ? 'green' : ($session->status === 'in_progress' ? 'yellow' : 'gray') }}-600">
                            {{ __($session->status) }}
                        </span>
                    </p>
                </div>

                {{-- Botón de control principal --}}
                @if ($session->status === 'pending')
                    <button wire:click="startSession"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                        ▶️ Comenzar entrenamiento
                    </button>
                @elseif($session->status === 'in_progress')
                    <button wire:click="finishSession"
                        class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                        ✅ Finalizar entrenamiento
                    </button>
                @else
                    <span class="text-green-600 font-semibold">✔ Completado</span>
                @endif
            </div>

            <hr>

            {{-- BLOQUES --}}
            @foreach ($blocks as $block)
                <div class="border rounded-lg bg-gray-50 p-4 space-y-3">
                    <h3 class="font-semibold text-gray-700 uppercase text-sm">
                        {{ $block->type->label() }}
                        @if ($block->name)
                            • {{ $block->name }}
                        @endif
                    </h3>

                    {{-- ITEMS (EJERCICIOS) --}}
                    @forelse($block->items as $item)
                        <div class="p-3 border rounded-lg bg-white shadow-sm space-y-2">
                            <div class="flex justify-between items-center">
                                <p class="font-medium text-gray-800">
                                    {{ $item->exercise?->name ?? 'Ejercicio sin nombre' }}
                                </p>
                                <span class="text-xs text-gray-500">
                                    {{ $item->sets ?? '?' }}x{{ $item->reps ?? '?' }} reps
                                </span>
                            </div>

                            @if ($item->notes)
                                <p class="text-xs text-gray-500 italic">{{ $item->notes }}</p>
                            @endif

                            {{-- SETS --}}
                            @php
                                $itemSets = $session->sets->where('plan_item_id', $item->id);
                            @endphp

                            @if ($itemSets->isEmpty())
                                <p class="text-xs text-gray-400">Sin sets generados</p>
                            @else
                                <div class="space-y-2">
                                    @foreach ($itemSets as $set)
                                        <div
                                            class="flex justify-between items-center rounded-md p-3 border
        {{ $set->completed_at ? 'bg-green-50 border-green-200' : 'bg-white border-gray-200' }}">

                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-800">
                                                    Set {{ $set->set_number }}
                                                </span>

                                                <span class="text-xs text-gray-600">
                                                    🎯 Objetivo: {{ $set->target_reps ?? '—' }} reps
                                                    @if ($item->load_prescription)
                                                        • Carga: {{ $item->load_prescription['load'] ?? '—' }} kg
                                                    @endif
                                                    • Descanso: {{ $set->target_rest_sec ?? 60 }}s
                                                </span>

                                                @if ($item->notes)
                                                    <span class="text-xs italic text-gray-500 mt-1">
                                                        💡 {{ $item->notes }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if (!$set->completed_at && $session->status !== 'completed')
                                                <button wire:click="completeSet({{ $set->id }})"
                                                    class="bg-green-500 hover:bg-green-600 text-white text-xs px-3 py-1 rounded-md transition">
                                                    ✅ Marcar como hecho
                                                </button>
                                            @else
                                                <span class="text-green-600 text-xs font-semibold flex items-center">
                                                    ✅ Completado
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach

                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No hay ejercicios en este bloque</p>
                    @endforelse
                </div>
            @endforeach

        </div>
    @endif

    <!-- Modal moderno de entrenamiento completado -->
    <div x-data="{ open: @entangle('showCompletionModal') }" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center relative">
            <h2 class="text-2xl font-semibold text-gray-800 mb-2">🎉 ¡Entrenamiento completado!</h2>
            <p class="text-gray-600 mb-4">
                ¡Excelente trabajo hoy! Completaste tu sesión.
                Recordá hidratarte y estirar los músculos 💪
            </p>

            <div class="flex justify-center gap-3 mt-6">
                <button wire:click="finishDay"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition">
                    Finalizar por hoy
                </button>
                <button wire:click="startNextSession"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition">
                    Hacer otro entrenamiento
                </button>
            </div>


            <button @click="open = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
                ✕
            </button>
        </div>
    </div>


</div>
