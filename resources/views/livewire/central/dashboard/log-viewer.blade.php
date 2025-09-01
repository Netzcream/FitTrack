<section class="w-full">
    <flux:heading size="xl" level="1">{{ __('Log Viewer') }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">
        {{ __('Gestor y visualización de logs') }}
    </flux:subheading>
    <flux:separator variant="subtle" />

    <div class="mt-6 grid grid-cols-1 md:grid-cols-12 gap-6">
        {{-- Sidebar de logs --}}



        <aside class="md:col-span-2">




            <flux:navlist>
                <flux:navlist.group heading="Archivos">
                    @foreach ($files as $file)
                        <flux:navlist.item class="cursor-pointer" :current="$file === $selectedFile" variant="outline"
                            wire:click="selectFile('{{ $file }}')" wire:navigate>
                            {{ $file }}
                        </flux:navlist.item>
                    @endforeach
                </flux:navlist.group>

            </flux:navlist>
        </aside>

        {{-- Contenido del log --}}
        <main class="md:col-span-10 space-y-4">
            @if ($selectedFile)
                <div class="flex items-center justify-between">
                    <flux:text size="md" class="font-semibold">
                        Log activo: <span class="font-mono">{{ $selectedFile }}</span>
                    </flux:text>

                    <div class="flex gap-4 items-center">
                        <flux:button variant="danger" wire:click="clear" size="sm">
                            Borrar
                        </flux:button>

                        <a href="{{ route('central.dashboard.log-viewer.show',$selectedFile)  }}"
                            class="text-sm text-blue-600 dark:text-blue-400 hover:underline" target="_blank">
                            Ver en nueva pestaña
                        </a>
                    </div>
                </div>
            @endif

            <div
                class="bg-gray-900 text-green-300 p-4 rounded-lg text-sm font-mono overflow-auto max-h-[75vh] whitespace-pre-wrap leading-relaxed border border-gray-700">{{ $content }}</div>
        </main>
    </div>
</section>
