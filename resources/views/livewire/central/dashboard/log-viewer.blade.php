<section class="w-full">
    <flux:heading size="xl" level="1">{{ __('Log Viewer') }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">
        {{ __('Gestor y visualización de logs con análisis en tiempo real') }}
    </flux:subheading>
    <flux:separator variant="subtle" />

    <div class="mt-6 grid grid-cols-1 md:grid-cols-12 gap-6">
        {{-- Sidebar de logs --}}
        <aside class="md:col-span-2">
            <div class="space-y-1">
                <div class="text-xs font-semibold text-gray-500 dark:text-neutral-400 px-3 py-2">Archivos</div>
                @foreach ($files as $file)
                    <button
                        wire:click="selectFile('{{ $file }}')"
                        class="w-full text-left px-3 py-2 text-sm rounded transition-colors cursor-pointer {{ $file === $selectedFile ? 'bg-gray-100 dark:bg-neutral-800 font-medium text-gray-900 dark:text-neutral-100' : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-50 dark:hover:bg-neutral-800/50' }}"
                    >
                        {{ $file }}
                    </button>
                @endforeach
            </div>
        </aside>

        {{-- Contenido del log --}}
        <main class="md:col-span-10 space-y-4">
            @if ($selectedFile)
                {{-- Header --}}
                <div class="flex items-center justify-between">
                    <flux:text size="md" class="font-semibold">
                        Log activo: <span class="font-mono">{{ $selectedFile }}</span>
                    </flux:text>

                    <div class="flex gap-2 items-center">
                        <flux:button
                            variant="outline"
                            wire:click="exportCsv"
                            size="sm"
                            icon="arrow-down-tray"
                        >
                            CSV
                        </flux:button>

                        <flux:button
                            variant="outline"
                            wire:click="downloadLog"
                            size="sm"
                            icon="document-arrow-down"
                        >
                            Descargar
                        </flux:button>

                        {{-- Control de actualización --}}
                        <flux:select
                            wire:model.live="refreshInterval"
                            wire:change="setRefreshInterval($event.target.value)"
                            size="sm"
                            class="w-20"
                        >
                            <option value="3">3s</option>
                            <option value="5">5s</option>
                            <option value="10">10s</option>
                            <option value="30">30s</option>
                            <option value="0">Off</option>
                        </flux:select>

                        <flux:button variant="ghost" wire:click="clear" size="sm" icon="trash">
                            Eliminar
                        </flux:button>
                    </div>
                </div>

                {{-- Estadísticas rápidas --}}
                @php
                    $stats = $this->getLogStats();
                @endphp
                <div class="grid grid-cols-4 gap-3">
                    <div class="border border-gray-200 dark:border-neutral-700 rounded p-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-gray-400 dark:bg-neutral-500"></div>
                            <div class="text-gray-600 dark:text-neutral-400 text-xs font-semibold">LÍNEAS TOTALES</div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-neutral-100 mt-1">{{ $stats['total_lines'] }}</div>
                    </div>
                    <div class="border border-gray-200 dark:border-neutral-700 rounded p-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-red-500"></div>
                            <div class="text-gray-600 dark:text-neutral-400 text-xs font-semibold">ERRORES</div>
                        </div>
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['errors'] }}</div>
                    </div>
                    <div class="border border-gray-200 dark:border-neutral-700 rounded p-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                            <div class="text-gray-600 dark:text-neutral-400 text-xs font-semibold">ADVERTENCIAS</div>
                        </div>
                        <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['warnings'] }}</div>
                    </div>
                    <div class="border border-gray-200 dark:border-neutral-700 rounded p-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-gray-400 dark:bg-neutral-500"></div>
                            <div class="text-gray-600 dark:text-neutral-400 text-xs font-semibold">INFO</div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-neutral-100 mt-1">{{ $stats['info'] }}</div>
                    </div>
                </div>

                {{-- Búsqueda y Filtros --}}
                <div class="flex gap-2 items-center justify-between">
                    {{-- Lado izquierdo: búsqueda y filtros --}}
                    <div class="flex gap-2 items-center flex-wrap">
                        <div class="w-48">
                            <flux:input
                                wire:model.live="searchQuery"
                                wire:change="updateSearch"
                                type="text"
                                placeholder="Buscar..."
                                size="sm"
                            />
                        </div>

                        {{-- Filtro por nivel --}}
                        <div class="flex gap-0.5 bg-neutral-100 dark:bg-neutral-800 p-0.5 rounded">
                            <button
                                wire:click="setLevelFilter('all')"
                                class="px-2 py-1 text-[11px] rounded transition {{ $levelFilter === 'all' ? 'bg-white dark:bg-neutral-700 font-semibold' : 'hover:bg-neutral-200 dark:hover:bg-neutral-700' }}"
                            >
                                Todos
                            </button>
                            <button
                                wire:click="setLevelFilter('error')"
                                class="px-2 py-1 text-[11px] rounded transition {{ $levelFilter === 'error' ? 'bg-red-500 text-white font-semibold' : 'text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30' }}"
                            >
                                ERROR
                            </button>
                            <button
                                wire:click="setLevelFilter('warning')"
                                class="px-2 py-1 text-[11px] rounded transition {{ $levelFilter === 'warning' ? 'bg-amber-500 text-white font-semibold' : 'text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/30' }}"
                            >
                                WARNING
                            </button>
                            <button
                                wire:click="setLevelFilter('info')"
                                class="px-2 py-1 text-[11px] rounded transition {{ $levelFilter === 'info' ? 'bg-neutral-500 text-white font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700' }}"
                            >
                                INFO
                            </button>
                            <button
                                wire:click="setLevelFilter('debug')"
                                class="px-2 py-1 text-[11px] rounded transition {{ $levelFilter === 'debug' ? 'bg-neutral-500 text-white font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700' }}"
                            >
                                DEBUG
                            </button>
                        </div>

                        @if ($searchQuery)
                            <flux:button
                                variant="ghost"
                                wire:click="clearSearch"
                                size="sm"
                                icon="x-mark"
                            />
                        @endif
                    </div>

                    {{-- Lado derecho: nueva pestaña y final --}}
                    <div class="flex gap-2 items-center">
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="arrow-top-right-on-square"
                            as="a"
                            href="{{ route('central.dashboard.log-viewer.show',$selectedFile)  }}"
                            target="_blank"
                        >
                            Nueva pestaña
                        </flux:button>

                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="arrow-down"
                            x-data
                            @click="let el = document.getElementById('log-container'); el.scrollTop = el.scrollHeight; window.scrollTo({top: document.body.scrollHeight, behavior: 'smooth'});"
                            title="Ir al final"
                        >
                            Final
                        </flux:button>
                    </div>
                </div>

                {{-- Viewer con numeración --}}
                <div
                    id="log-container"
                    class="bg-neutral-950 text-neutral-100 p-4 rounded-lg text-xs font-mono overflow-auto max-h-[75vh] border border-neutral-700"
                    @if($refreshInterval > 0)
                        wire:poll.{{ $refreshInterval }}s="loadContent"
                    @endif
                    x-data="{ shouldScroll: @entangle('autoScroll') }"
                    x-init="
                        $watch('shouldScroll', value => {
                            if (value) {
                                $nextTick(() => {
                                    $el.scrollTop = $el.scrollHeight;
                                });
                            }
                        });
                        window.Livewire.on('content-updated', () => {
                            if (shouldScroll) {
                                $nextTick(() => {
                                    $el.scrollTop = $el.scrollHeight;
                                });
                            }
                        });
                    "
                >
                    @php
                        $lines = array_filter(explode("\n", $content), fn($l) => trim($l) !== '');
                    @endphp
                    @foreach ($lines as $index => $line)
                        <div class="flex hover:bg-neutral-900 transition-colors" style="line-height: 1.3;">
                            <span class="text-neutral-600 inline-block w-12 text-right pr-4 select-none">{{ $index + 1 }}</span>
                            <span class="flex-1 whitespace-pre-wrap
                                @if (str_contains($line, 'ERROR')) text-red-400 font-semibold
                                @elseif (str_contains($line, 'WARNING') || str_contains($line, 'WARN')) text-amber-400 font-semibold
                                @elseif (str_contains($line, 'INFO')) text-gray-300
                                @elseif (str_contains($line, 'DEBUG')) text-gray-400
                                @else text-gray-300
                                @endif
                            ">{{ $line }}</span>
                        </div>
                    @endforeach
                    <div id="log-content"></div>
                </div>

                @if (empty(trim($content)))
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-center py-8">
                        <flux:text size="sm" class="text-gray-500">
                            El log está vacío
                        </flux:text>
                    </div>
                @endif
            @endif
        </main>
    </div>
</section>
