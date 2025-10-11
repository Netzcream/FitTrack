<x-layouts.tenant>
    @php
        $active = $active ?? 'training';
        $overdueInvoices = $overdueInvoices ?? 0;
        $aptExpiresInDays = $aptExpiresInDays ?? null;
        $unreadMessages = $unreadMessages ?? 0;
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-[16rem_1fr] gap-8">
        <aside class="space-y-4">
            <div class="p-4 rounded-xl border dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    @if ($student?->hasMedia('avatar'))
                        <img src="{{ $student->getFirstMediaUrl('avatar', 'thumb') }}" alt="{{ $student->full_name }}"
                            class="h-12 w-12 rounded-full object-cover">
                    @else
                        @php
                            $i1 = mb_substr($student->first_name ?? '', 0, 1);
                            $i2 = mb_substr($student->last_name ?? '', 0, 1);
                        @endphp
                        <div
                            class="h-12 w-12 rounded-full bg-muted flex items-center justify-center text-sm font-semibold">
                            {{ trim($i1 . $i2) ?: '??' }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $student->full_name }}</div>
                        <div class="text-xs text-muted-foreground">{{ __('ID') }}:
                            {{ \Illuminate\Support\Str::limit($student->uuid, 8, '') }}</div>
                        <div class="text-xs mt-0.5">
                            <span class="inline-flex items-center gap-1">
                                <span
                                    class="inline-block h-2 w-2 rounded-full {{ ($student->status ?? 'inactive') === 'active' ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                {{ __('site.' . $student->status ?? 'inactive') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $items = [
                    [
                        'key' => 'tenant.dashboard.students.training',
                        'label' => __('Entrenamiento'),
                        'badge' => null,
                    ],
                    [
                        'key' => 'tenant.dashboard.students.profile',
                        'label' => __('Datos personales'),
                        'badge' => null,
                    ],
                    [
                        'key' => 'tenant.dashboard.students.finance',
                        'label' => __('Finanzas'),
                        'badge' => $overdueInvoices ?: null,
                    ],
                    [
                        'key' => 'tenant.dashboard.students.health',
                        'label' => __('Salud & apto'),
                        'badge' => $aptExpiresInDays !== null && $aptExpiresInDays <= 7 ? __('¡Vence!') : null,
                    ],
                    [
                        'key' => 'tenant.dashboard.students.metrics',
                        'label' => __('Métricas'),
                        'href' => route('tenant.dashboard.students.metrics', $student),
                        'badge' => null,
                    ],
                    [
                        'key' => 'tenant.dashboard.students.files',
                        'label' => __('Archivos'),
                        'badge' => null,
                    ],
                    [
                        'key' => 'tenant.dashboard.students.messages',
                        'label' => __('Mensajes'),
                        'badge' => $unreadMessages ?: null,
                    ],
                ];
            @endphp

            <nav class="space-y-1">
                <a href="{{ route('tenant.dashboard.students.index') }}"
                    class="block px-3 py-2 text-xs text-muted-foreground hover:underline">
                    ← {{ __('Volver a alumnos') }}
                </a>
                @foreach ($items as $it)
                    <a href="{{ route($it['key'], $student) }}"
                        class="flex items-center justify-between px-3 py-2 rounded-md transition
                  {{ request()->routeIs($it['key']) ? 'bg-zinc-800/5 dark:bg-white/[7%] font-medium' : 'hover:bg-zinc-800/5 dark:hover:bg-white/[7%]' }}">
                        <span>{{ $it['label'] }}</span>
                        @if ($it['badge'])
                            <span
                                class="text-xs px-2 py-0.5 rounded-full bg-primary/10 text-primary">{{ $it['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </aside>

        <main class="max-w-6xl space-y-6">
            {{-- Encabezado moderno: 3 métricas unificadas en una sola “píldora” --}}
            <div class="rounded-xl border dark:border-zinc-700 bg-muted/30 p-2">
                <div class="flex flex-col md:flex-row gap-2">
                    <div class="flex-1 rounded-xl px-4 py-3">
                        <div class="text-[11px] uppercase tracking-wide text-muted-foreground">{{ __('IMC') }}
                        </div>
                        <div class="text-lg font-semibold leading-tight">{{ $student->imc ?? '—' }}</div>
                    </div>
                    <div class="flex-1 rounded-xl px-4 py-3">
                        <div class="text-[11px] uppercase tracking-wide text-muted-foreground">{{ __('Apto físico') }}
                        </div>
                        <div class="text-lg font-semibold leading-tight">
                            @if ($student->apt_fitness_expires_at)
                                {{ __('Vence el') }} {{ $student->apt_fitness_expires_at->isoFormat('D MMM') }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div class="flex-1 rounded-xl px-4 py-3">
                        <div class="text-[11px] uppercase tracking-wide text-muted-foreground">
                            {{ __('Último acceso') }}</div>
                        <div class="text-lg font-semibold leading-tight">
                            {{ optional($student->last_login_at)->diffForHumans() ?? '—' }}</div>
                    </div>
                </div>
            </div>

            {{ $slot }}
        </main>
    </div>

</x-layouts.tenant>
