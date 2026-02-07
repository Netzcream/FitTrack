<section class="w-full">
    <div
        class="rounded-lg border
                        border-gray-200 dark:border-neutral-700
                        bg-white dark:bg-neutral-900
                        overflow-hidden">

        {{-- Header --}}
        <div class="p-6">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div>
                    <flux:heading size="lg" level="2">{{ __('Tareas en cola') }}</flux:heading>
                    <flux:subheading size="sm" class="mt-1">{{ __('Gestión de jobs pendientes y fallidos') }}</flux:subheading>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="rounded-lg bg-blue-50 dark:bg-blue-950/40 p-3 border border-blue-200 dark:border-blue-900">
                    <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">{{ __('Pendientes') }}</div>
                    <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $stats['pending'] ?? 0 }}</div>
                </div>
                <div class="rounded-lg bg-red-50 dark:bg-red-950/40 p-3 border border-red-200 dark:border-red-900">
                    <div class="text-xs text-red-600 dark:text-red-400 font-medium">{{ __('Fallidos') }}</div>
                    <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $stats['failed'] ?? 0 }}</div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="flex flex-wrap gap-4 w-full items-end pb-6 border-b border-gray-200 dark:border-neutral-700">
                {{-- Status Filter --}}
                <div class="min-w-[160px]">
                    <flux:select size="sm" wire:model.live="filterStatus" label="{{ __('Estado') }}">
                        <flux:select.option value="pending">{{ __('Pendientes') }}</flux:select.option>
                        <flux:select.option value="failed">{{ __('Fallidos') }}</flux:select.option>
                        <flux:select.option value="all">{{ __('Todos') }}</flux:select.option>
                    </flux:select>
                </div>

                {{-- Queue Filter --}}
                @if (!empty($queues))
                <div class="min-w-[160px]">
                    <flux:select size="sm" wire:model.live="filterQueue" label="{{ __('Cola') }}">
                        <flux:select.option value="">{{ __('Todas las colas') }}</flux:select.option>
                        @foreach ($queues as $queue)
                            <flux:select.option value="{{ $queue }}">{{ $queue }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                @endif

                {{-- Actions --}}
                <div class="ml-auto flex gap-2">
                    <flux:button size="sm" variant="ghost" wire:click="resetFilters">
                        {{ __('Limpiar filtros') }}
                    </flux:button>

                    @if ($filterStatus === 'failed' && $stats['failed'] > 0)
                        <flux:button size="sm" variant="danger"
                                wire:click="clearAllFailed"
                                wire:confirm="{{ __('¿Estás seguro de que quieres eliminar todos los jobs fallidos?') }}">
                            {{ __('Limpiar fallidos') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Table --}}
        @if ($jobs->isEmpty())
            <div class="text-gray-500 dark:text-neutral-400 text-sm py-8 text-center">
                @if ($filterStatus === 'pending')
                    ✓ {{ __('No hay jobs pendientes actualmente') }}
                @elseif ($filterStatus === 'failed')
                    ✓ {{ __('No hay jobs fallidos') }}
                @else
                    ✓ {{ __('No hay jobs en el sistema') }}
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse text-left">
                    <thead class="text-gray-600 dark:text-neutral-400 border-b border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800/50">
                        <tr>
                            <th class="px-6 py-3 font-semibold">{{ __('ID') }}</th>
                            <th class="px-6 py-3 font-semibold">{{ __('Cola') }}</th>
                            <th class="px-6 py-3 font-semibold">{{ __('Job') }}</th>
                            <th class="px-6 py-3 font-semibold">{{ __('Creado') }}</th>
                            <th class="px-6 py-3 font-semibold">{{ __('Se ejecuta') }}</th>
                            <th class="px-6 py-3 font-semibold">{{ __('Estado') }}</th>
                            <th class="px-6 py-3 font-semibold text-right">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                        @foreach ($jobs as $job)
                            @php
                                $payload = json_decode($job->payload, true);
                                $displayName = $payload['displayName'] ?? 'N/A';
                                $attempts = $job->attempts ?? 0;
                            @endphp
                            <tr wire:key="job-{{ $job->id }}" class="hover:bg-gray-50 dark:hover:bg-neutral-800/50">
                                <td class="px-6 py-4 font-mono text-xs text-gray-600 dark:text-neutral-400">{{ $job->id }}</td>
                                <td class="px-6 py-4 text-xs">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-100 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 font-medium">
                                        {{ $job->queue }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 truncate max-w-[280px] text-gray-700 dark:text-neutral-300" title="{{ $displayName }}">
                                    {{ $displayName }}
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 dark:text-neutral-400">
                                    @php
                                        $createdDate = \Carbon\Carbon::parse($job->created_at ?? $job->failed_at);
                                    @endphp
                                    <span class="text-xs text-gray-700 dark:text-neutral-200">{{ $createdDate->format('d/m/Y H:i:s') }}</span><br>
                                    <span class="text-xs text-gray-500 dark:text-neutral-400">{{ $createdDate->diffForHumans() }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-600 dark:text-neutral-300">
                                    @php
                                        $timestamp = $job->available_at ?? ($job->created_at ?? 0);
                                        if ($timestamp) {
                                            $executeTime = is_numeric($timestamp)
                                                ? \Carbon\Carbon::createFromTimestamp($timestamp)
                                                : \Carbon\Carbon::parse($timestamp);
                                        }
                                    @endphp
                                    @if (isset($executeTime) && $executeTime)
                                        <span class="text-xs text-gray-700 dark:text-neutral-200">{{ $executeTime->format('d/m/Y H:i:s') }}</span><br>
                                        <span class="text-xs text-gray-500 dark:text-neutral-400">{{ $executeTime->diffForHumans() }}</span>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-neutral-500">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($filterStatus === 'pending' || (isset($job->status) && $job->status === 'pending'))
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 text-xs font-medium">
                                            {{ __('Pendiente') }}
                                        </span>
                                    @elseif ($filterStatus === 'failed' || (isset($job->status) && $job->status === 'failed'))
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-red-100 dark:bg-red-950/40 text-red-700 dark:text-red-300 text-xs font-medium">
                                            ✗ {{ __('Fallido') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    @php
                                        $isFailed = $filterStatus === 'failed' || (isset($job->status) && $job->status === 'failed');
                                    @endphp
                                    <flux:modal.trigger name="job-details">
                                        <flux:button size="sm" wire:click="showDetails('{{ $job->id }}', '{{ $isFailed ? 'failed' : 'pending' }}')" icon="eye">
                                            {{ __('Ver detalles') }}
                                        </flux:button>
                                    </flux:modal.trigger>
                                    @if (!$isFailed)
                                        <flux:button size="sm" wire:click="runNow('{{ $job->id }}')" icon="play-circle">
                                            {{ __('Ejecutar') }}
                                        </flux:button>
                                    @endif
                                    <flux:modal.trigger name="confirm-delete-job">
                                        <flux:button size="sm" variant="ghost" wire:click="confirmDelete('{{ $job->id }}', '{{ $isFailed ? 'failed' : 'pending' }}')" icon="trash">
                                            {{ __('Eliminar') }}
                                        </flux:button>
                                    </flux:modal.trigger>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="mt-6 px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
                {{ $jobs->links('components.preline.pagination') }}
            </div>
        @endif
    </div>

    {{-- Modal de confirmación de eliminación (único) --}}
    <flux:modal name="confirm-delete-job" class="min-w-[22rem]" x-data
        @job-deleted.window="$dispatch('modal-close', { name: 'confirm-delete-job' })">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Eliminar job?') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Esta acción no se puede deshacer.') }}</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">
                    {{ __('Sí, eliminar') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal de detalles del job --}}
    <flux:modal name="job-details" class="min-w-[42rem]">
        @if ($selectedJobDetails)
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Detalles del Job') }}</flux:heading>
                <flux:subheading class="mt-1">{{ $selectedJobDetails['displayName'] ?? 'Job' }}</flux:subheading>
            </div>

            {{-- Información básica --}}
            <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-neutral-800/50 rounded-lg">
                <div>
                    <flux:text class="text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase">{{ __('ID') }}</flux:text>
                    <flux:text class="font-mono text-sm mt-1">{{ $selectedJobDetails['id'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase">{{ __('Cola') }}</flux:text>
                    <flux:text class="font-mono text-sm mt-1">{{ $selectedJobDetails['queue'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase">{{ __('Estado') }}</flux:text>
                    <flux:text class="font-mono text-sm mt-1">
                        @if ($selectedJobDetails['type'] === 'failed')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-red-100 dark:bg-red-950/40 text-red-700 dark:text-red-300 text-xs font-medium">
                                ✗ {{ __('Fallido') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 text-xs font-medium">
                                {{ __('Pendiente') }}
                            </span>
                        @endif
                    </flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase">{{ __('Intentos') }}</flux:text>
                    <flux:text class="font-mono text-sm mt-1">{{ $selectedJobDetails['attempts'] }}</flux:text>
                </div>
                @if ($selectedJobDetails['created_at'])
                <div>
                    <flux:text class="text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase">{{ __('Creado') }}</flux:text>
                    <flux:text class="font-mono text-sm mt-1">{{ \Carbon\Carbon::parse($selectedJobDetails['created_at'])->format('Y-m-d H:i:s') }}</flux:text>
                </div>
                @endif
                @if ($selectedJobDetails['failed_at'])
                <div>
                    <flux:text class="text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase">{{ __('Falló') }}</flux:text>
                    <flux:text class="font-mono text-sm mt-1">{{ \Carbon\Carbon::parse($selectedJobDetails['failed_at'])->format('Y-m-d H:i:s') }}</flux:text>
                </div>
                @endif
            </div>

            {{-- Tab Navigation --}}
            <div class="flex gap-2 border-b border-gray-200 dark:border-neutral-700">
                <button
                    wire:click="$set('detailsTab', 'payload')"
                    class="px-4 py-2 text-sm font-medium transition-colors
                        {{ $detailsTab === 'payload'
                            ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400'
                            : 'text-gray-600 dark:text-neutral-400 hover:text-gray-900 dark:hover:text-neutral-200' }}">
                    <x-icons.lucide.file-text class="inline w-4 h-4 mr-1" />
                    {{ __('Payload') }}
                </button>

                <button
                    wire:click="$set('detailsTab', 'raw')"
                    class="px-4 py-2 text-sm font-medium transition-colors
                        {{ $detailsTab === 'raw'
                            ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400'
                            : 'text-gray-600 dark:text-neutral-400 hover:text-gray-900 dark:hover:text-neutral-200' }}">
                    <x-icons.lucide.code class="inline w-4 h-4 mr-1" />
                    {{ __('JSON Raw') }}
                </button>

                @if ($selectedJobDetails['handler'])
                <button
                    wire:click="$set('detailsTab', 'handler')"
                    class="px-4 py-2 text-sm font-medium transition-colors
                        {{ $detailsTab === 'handler'
                            ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400'
                            : 'text-gray-600 dark:text-neutral-400 hover:text-gray-900 dark:hover:text-neutral-200' }}">
                    <x-icons.lucide.cog class="inline w-4 h-4 mr-1" />
                    {{ __('Handler') }}
                </button>
                @endif

                @if ($selectedJobDetails['exception'] && $selectedJobDetails['type'] === 'failed')
                <button
                    wire:click="$set('detailsTab', 'exception')"
                    class="px-4 py-2 text-sm font-medium transition-colors
                        {{ $detailsTab === 'exception'
                            ? 'text-red-600 dark:text-red-400 border-b-2 border-red-600 dark:border-red-400'
                            : 'text-gray-600 dark:text-neutral-400 hover:text-gray-900 dark:hover:text-neutral-200' }}">
                    <x-icons.lucide.circle-alert class="inline w-4 h-4 mr-1" />
                    {{ __('Error') }}
                </button>
                @endif
            </div>

            {{-- Tab Content --}}
            <div class="overflow-hidden">
                @if ($detailsTab === 'payload')
                <div class="p-4 bg-gray-50 dark:bg-neutral-800/50 rounded-lg overflow-auto max-h-96">
                    <pre class="text-xs text-gray-700 dark:text-neutral-300 font-mono whitespace-pre-wrap break-words">{{ json_encode($selectedJobDetails['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
                @endif

                @if ($detailsTab === 'raw')
                @if ($selectedJobDetails['commandContent'])
                <div class="p-4 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900 rounded-lg overflow-auto max-h-96">
                    <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase mb-3">{{ __('Command') }}</div>
                    @if (is_array($selectedJobDetails['commandContent']))
                        <pre class="text-xs text-blue-900 dark:text-blue-200 font-mono whitespace-pre-wrap overflow-x-auto">{{ json_encode($selectedJobDetails['commandContent'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    @else
                        <pre class="text-xs text-blue-900 dark:text-blue-200 font-mono whitespace-pre-wrap overflow-x-auto">{{ $selectedJobDetails['commandContent'] }}</pre>
                    @endif
                </div>
                @endif
                @endif

                @if ($detailsTab === 'handler' && $selectedJobDetails['handler'])
                <div class="p-4 bg-gray-50 dark:bg-neutral-800/50 rounded-lg overflow-auto max-h-96">
                    <flux:text class="text-sm text-gray-700 dark:text-neutral-300 font-mono">{{ $selectedJobDetails['handler'] }}</flux:text>
                </div>
                @endif

                @if ($detailsTab === 'exception' && $selectedJobDetails['exception'] && $selectedJobDetails['type'] === 'failed')
                <div class="p-4 bg-red-50 dark:bg-red-950/20 rounded-lg overflow-auto max-h-96">
                    <pre class="text-xs text-red-700 dark:text-red-300 font-mono whitespace-pre-wrap break-words">{{ $selectedJobDetails['exception'] }}</pre>
                </div>
                @endif
            </div>

            {{-- Acciones --}}
            <div class="flex gap-2 justify-end pt-4 border-t border-gray-200 dark:border-neutral-700">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cerrar') }}</flux:button>
                </flux:modal.close>
                @if ($selectedJobDetails['type'] !== 'failed')
                <flux:button wire:click="runNow('{{ $selectedJobDetails['id'] }}')" icon="play-circle">
                    {{ __('Ejecutar ahora') }}
                </flux:button>
                @endif
            </div>
        </div>
        @endif
    </flux:modal>
</section>
