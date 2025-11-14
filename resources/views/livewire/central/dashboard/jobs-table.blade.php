<section class="w-full">
    <div
        class="rounded-2xl border
                        border-zinc-200 dark:border-white/10
                        bg-zinc-50/80 dark:bg-white/[0.04]
                        p-6 shadow-sm hover:shadow-md transition-all duration-300">
        <div class="mb-4">
            <div class="text-lg font-semibold text-zinc-800 dark:text-white">Tareas en cola</div>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                Últimos 20 jobs pendientes de ejecución
            </div>
        </div>


        @if ($jobs->isEmpty())
            <div class="text-zinc-500 dark:text-zinc-400 text-sm">
                No hay jobs pendientes actualmente.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse text-left">
                    <thead class="text-zinc-600 dark:text-zinc-400 border-b border-zinc-200 dark:border-white/10">
                        <tr>
                            <th class="px-4 py-2 font-semibold">ID</th>
                            <th class="px-4 py-2 font-semibold">Cola</th>
                            <th class="px-4 py-2 font-semibold">Job</th>
                            <th class="px-4 py-2 font-semibold">Creado</th>
                            <th class="px-4 py-2 font-semibold">Disponible</th>
                            <th class="px-4 py-2 font-semibold">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jobs as $job)
                            @php
                                $payload = json_decode($job->payload, true);
                                $displayName = $payload['displayName'] ?? 'N/A';
                            @endphp
                            <tr class="border-t border-zinc-200 dark:border-white/10">
                                <td class="px-4 py-2">{{ $job->id }}</td>
                                <td class="px-4 py-2">{{ $job->queue }}</td>
                                <td class="px-4 py-2 truncate max-w-[280px]" title="{{ $displayName }}">
                                    {{ $displayName }}</td>
                                <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">
                                    {{ \Carbon\Carbon::parse($job->created_at)->diffForHumans() }}
                                </td>
                                <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">
                                    {{ \Carbon\Carbon::createFromTimestamp($job->available_at)->diffForHumans() }}
                                </td>
                                <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">
                                    <flux:button wire:click="runNow('{{ $job->id }}')" size="sm"
                                        icon="play-circle">
                                        Ejecutar ahora
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
