<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                Workouts de {{ $student->name }}
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Historial de sesiones de entrenamiento
            </p>
        </div>
        <a href="{{ route('tenant.dashboard.workouts.create', ['studentId' => $student->id]) }}"
           class="btn btn-primary btn-sm">
            <x-heroicon-o-plus class="w-4 h-4 mr-1" />
            Nuevo Workout
        </a>
    </div>

    {{-- Stats mini --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-xs text-gray-600 dark:text-gray-400">Total</div>
            <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-xs text-gray-600 dark:text-gray-400">Este Mes</div>
            <div class="text-xl font-bold text-blue-600">{{ $stats['thisMonth'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-xs text-gray-600 dark:text-gray-400">Rating Prom.</div>
            <div class="text-xl font-bold text-yellow-600">{{ $stats['avgRating'] ?? '-' }}</div>
        </div>
    </div>

    {{-- Filtros de fecha --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="label label-text text-xs">Desde</label>
                <input type="date" wire:model.live="filterDateFrom" class="input input-sm input-bordered w-full">
            </div>
            <div>
                <label class="label label-text text-xs">Hasta</label>
                <input type="date" wire:model.live="filterDateTo" class="input input-sm input-bordered w-full">
            </div>
        </div>
    </div>

    {{-- Lista de workouts --}}
    <div class="space-y-3">
        @forelse($workouts as $workout)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold">{{ $workout->date->format('d/m/Y') }}</span>
                            @php
                                $statusColors = [
                                    'completed' => 'badge-success',
                                    'in_progress' => 'badge-warning',
                                    'pending' => 'badge-info',
                                    'skipped' => 'badge-error',
                                ];
                            @endphp
                            <span class="badge badge-xs {{ $statusColors[$workout->status] ?? '' }}">
                                {{ $workout->status }}
                            </span>
                            @if($workout->rating)
                                <div class="flex items-center">
                                    @for($i = 1; $i <= $workout->rating; $i++)
                                        <x-heroicon-s-star class="w-3 h-3 text-yellow-400" />
                                    @endfor
                                </div>
                            @endif
                        </div>

                        @if($workout->trainingPlan)
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Plan: {{ $workout->trainingPlan->name }}
                            </div>
                        @endif

                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ count($workout->exercises_data ?? []) }} ejercicios
                            @if($workout->duration_minutes)
                                • {{ $workout->duration_minutes }} min
                            @endif
                        </div>

                        @if($workout->notes)
                            <div class="text-sm text-gray-500 italic mt-1">
                                {{ Str::limit($workout->notes, 80) }}
                            </div>
                        @endif
                    </div>

                    <div class="flex gap-1">
                        <button wire:click="quickClone({{ $workout->id }})"
                                class="btn btn-xs btn-ghost"
                                title="Clonar para hoy">
                            <x-heroicon-o-document-duplicate class="w-4 h-4" />
                        </button>
                        <a href="{{ route('tenant.dashboard.workouts.edit', $workout) }}"
                           class="btn btn-xs btn-ghost"
                           title="Editar">
                            <x-heroicon-o-pencil class="w-4 h-4" />
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center text-gray-500">
                <x-heroicon-o-clipboard-document-list class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                <p>No hay workouts registrados</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $workouts->links() }}
    </div>
</div>
