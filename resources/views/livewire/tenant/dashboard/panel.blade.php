<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4">

        {{-- KPIs (estilo ejemplo) --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <a wire:navigate href="{{ route('tenant.dashboard.students.index') }}" class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 flex flex-col">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-neutral-500">{{ __('site.active_students') }}</p>
                        <p class="mt-1 text-2xl font-semibold">{{ number_format($publishedCount) }}</p>
                    </div>
                    <div class="rounded-lg bg-neutral-100 dark:bg-neutral-800 p-2">
                        <flux:icon.users class="size-5" />
                    </div>
                </div>

                <p class="text-xs text-neutral-500 mt-auto pt-3">{{ __('site.updated') }}: <span
                        class="font-medium text-neutral-700 dark:text-neutral-300">hoy</span></p>
            </a>

            {{-- Rutinas en curso: removido según requerimiento --}}

            {{-- Chats con Alumnos (no leídos) --}}
            <a wire:navigate href="{{ route('tenant.dashboard.messages.conversations.index') }}" class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 flex flex-col">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-neutral-500">Chats con alumnos (no leídos)</p>
                        <p class="mt-1 text-2xl font-semibold">{{ number_format($unreadStudentMessages) }}</p>
                    </div>
                    <div class="rounded-lg bg-neutral-100 dark:bg-neutral-800 p-2">
                        <flux:icon.inbox class="size-5" />
                    </div>
                </div>
                <p class="text-xs text-neutral-500 mt-auto pt-3">{{ __('site.contacts_today') }}:
                    <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $contactsToday }}</span>
                </p>
            </a>

            {{-- Mensajes de Soporte (no leídos) --}}
            <a wire:navigate href="{{ route('tenant.dashboard.support.show') }}" class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 flex flex-col">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-neutral-500">Mensajes de soporte (no leídos)</p>
                        <p class="mt-1 text-2xl font-semibold">{{ number_format($unreadSupport) }}</p>
                    </div>
                    <div class="rounded-lg bg-neutral-100 dark:bg-neutral-800 p-2">
                        <flux:icon.life-buoy class="size-5" />
                    </div>
                </div>
                <p class="text-xs text-neutral-500 mt-auto pt-3">{{ __('site.updated') }}: <span
                        class="font-medium text-neutral-700 dark:text-neutral-300">hoy</span></p>
            </a>

            {{-- Contactos desde la web (pendientes) O Mini Gráfico de Uso de IA --}}
            @if ($hasAiAccess)
                {{-- Card con mini gráfico de uso de IA --}}
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 flex flex-col">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-xs text-neutral-500">Uso de IA este mes</p>
                            <p class="mt-1 text-2xl font-semibold">{{ $aiUsage['used'] ?? 0 }}<span class="text-sm text-neutral-500">/{{ $aiUsage['limit'] ?? 0 }}</span></p>
                        </div>
                        <div class="rounded-lg bg-violet-100 dark:bg-violet-900/30 p-2">
                            <flux:icon.sparkles class="size-5 text-violet-600 dark:text-violet-400" />
                        </div>
                    </div>

                    {{-- Mini barra de progreso --}}
                    <div class="w-full bg-neutral-200 dark:bg-neutral-800 rounded-full h-1.5 mb-3">
                        <div class="h-full rounded-full transition-all {{ ($aiUsage['percentage'] ?? 0) >= 90 ? 'bg-red-500' : (($aiUsage['percentage'] ?? 0) >= 70 ? 'bg-amber-500' : 'bg-violet-600') }}"
                             style="width: {{ min(100, $aiUsage['percentage'] ?? 0) }}%">
                        </div>
                    </div>

                    {{-- Mini gráfico de tendencia (últimos 6 meses) --}}
                    @if (!empty($aiChartData['labels']))
                        <div id="ai-usage-mini-chart"
                             data-apex-placeholder
                             data-categories='@json($aiChartData['labels'])'
                             data-series='@json($aiChartData['series'])'
                             data-chart-type="line"
                             data-chart-height="80"
                             data-chart-colors='["#8b5cf6", "#d1d5db"]'
                             data-stroke-width="2"
                             data-chart-toolbar="false"
                             class="h-20 -mb-2">
                        </div>
                    @else
                        <p class="text-xs text-neutral-500 text-center py-4">Sin datos históricos aún</p>
                    @endif
                </div>
            @else
                {{-- Card original de contactos web --}}
                <a wire:navigate href="{{ route('tenant.dashboard.contacts.index') }}" class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 flex flex-col">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-neutral-500">Contactos desde la web (pendientes)</p>
                            <p class="mt-1 text-2xl font-semibold">{{ number_format($webContactsPending) }}</p>
                        </div>
                        <div class="rounded-lg bg-neutral-100 dark:bg-neutral-800 p-2">
                            <flux:icon.globe class="size-5" />
                        </div>
                    </div>
                    <p class="text-xs text-neutral-500 mt-auto pt-3">{{ __('site.updated') }}: <span
                            class="font-medium text-neutral-700 dark:text-neutral-300">hoy</span></p>
                </a>
            @endif
        </div>

        {{-- Gráfico + Acciones rápidas (estilo ejemplo) --}}
        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Altas vs Bajas por semana (últimas 12) --}}
            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold">{{ __('site.new_students_weekly') }}</h3>
                    <a wire:navigate href="{{ route('tenant.dashboard.students.index') }}"
                        class="text-xs text-[var(--brand-700)] hover:underline">
                        {{ __('site.view_all') }}
                    </a>
                </div>

                <!-- Legend -->
                <div class="flex justify-center sm:justify-end items-center gap-x-4 mb-3 sm:mb-6 mt-2">
                    <div class="inline-flex items-center">
                        <span class="size-2.5 inline-block rounded-sm me-2" style="background-color: var(--ftt-color-base);"></span>
                        <span
                            class="text-[13px] text-gray-600 dark:text-neutral-400">{{ __('site.new_students') }}</span>
                    </div>
                </div>

                {{-- Contenedor del chart con data-* para JS --}}
                <div id="dash-students-inout-weekly" data-apex-placeholder
                    data-categories='@json($chartLabels ?? [])' data-series='@json($chartSeries ?? [])'
                    class="min-h-[300px]"></div>
            </div>



            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4">
                <h3 class="text-sm font-semibold mb-3">{{ __('site.quick_actions') }}</h3>
                <div class="grid grid-cols-2 gap-3">

                    {{-- Nuevo alumno (abre modal Flux) --}}
                    <flux:modal.trigger name="create-student">
                        <flux:button class="!h-auto w-full flex flex-col items-center justify-center py-6 text-center cursor-pointer"
                            type="button">
                            <flux:icon.user-plus class="size-6 mb-2" />
                            <span class="text-sm font-medium">{{ __('site.new_student') }}</span>
                        </flux:button>
                    </flux:modal.trigger>

                    {{-- Ir a alumnos --}}
                    <flux:button as="a" wire:navigate href="{{ route('tenant.dashboard.students.index') }}"
                        class="!h-auto flex flex-col items-center justify-center py-6 text-center">
                        <flux:icon.users class="size-6 mb-2" />
                        <span class="text-sm font-medium">{{ __('site.view_students') }}</span>
                    </flux:button>

                    {{-- Gestionar Ejercicios --}}
                    <flux:button as="a" wire:navigate href="{{ route('tenant.dashboard.exercises.index') }}"
                        class="!h-auto flex flex-col items-center justify-center py-6 text-center">
                        <flux:icon.dumbbell class="size-6 mb-2" />
                        <span class="text-sm font-medium">{{ __('site.exercises') }}</span>
                    </flux:button>

                    {{-- Gestionar Entrenamientos --}}
                    <flux:button as="a" wire:navigate href="{{ route('tenant.dashboard.training-plans.index') }}"
                        class="!h-auto flex flex-col items-center justify-center py-6 text-center">
                        <flux:icon.clipboard-list class="size-6 mb-2" />
                        <span class="text-sm font-medium">Gestionar entrenamientos</span>
                    </flux:button>

                    {{-- Soporte --}}
                    <flux:button as="a" wire:navigate href="{{ route('tenant.dashboard.support.show') }}"
                        class="!h-auto flex flex-col items-center justify-center py-6 text-center">
                        <flux:icon.life-buoy class="size-6 mb-2" />
                        <span class="text-sm font-medium">{{ __('Soporte') }}</span>
                    </flux:button>

                    {{-- Manuales y Guías --}}
                    <flux:button as="a" wire:navigate href="{{ route('tenant.dashboard.manuals.index') }}"
                        class="!h-auto flex flex-col items-center justify-center py-6 text-center">
                        <flux:icon.book-open class="size-6 mb-2" />
                        <span class="text-sm font-medium">{{ __('Manuales y Guías') }}</span>
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- Listas (recientes / top) tal cual estructura ejemplo, con placeholders por ahora) --}}
        <div class="grid gap-4 lg:grid-cols-3">
            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold">{{ __('site.recent_contacts') }}</h3>
                    <div class="flex items-center gap-2">
                        <flux:button size="sm" wire:click="markAllSupportAsRead">{{ __('site.mark_all_as_read') }}</flux:button>
                        <a wire:navigate href="{{ route('tenant.dashboard.contacts.index') }}"
                            class="text-xs text-[var(--brand-700)] hover:underline">Ver todos</a>
                    </div>
                </div>

                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left border-b border-neutral-200 dark:border-neutral-800 text-neutral-500">
                            <tr>
                                <th class="py-2 pr-3">{{ __('site.name') }}</th>
                                <th class="py-2 pr-3">{{ __('site.topic') }}</th>
                                <th class="py-2 pr-3">{{ __('site.when') }}</th>
                                <th class="py-2 text-right">{{ __('site.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                            <tr>
                                <td colspan="4" class="py-6 text-center text-neutral-500">
                                    {{ __('site.no_contacts_yet') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Top Paquetes: removido según requerimiento --}}
        </div>

        {{-- MODAL: Nuevo alumno (Flux) --}}
        <flux:modal name="create-student" class="min-w-[28rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('site.new_student') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('site.new_student_subheading') }}</flux:text>
                </div>

                <form wire:submit.prevent="saveStudent" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <flux:label class="text-xs">{{ __('site.first_name') }}</flux:label>
                            <flux:input wire:model.defer="first_name" autofocus />
                            @error('first_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <flux:label class="text-xs">{{ __('site.last_name') }}</flux:label>
                            <flux:input wire:model.defer="last_name" />
                            @error('last_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <flux:label class="text-xs">{{ __('site.phone') }}</flux:label>
                            <flux:input wire:model.defer="phone" />
                            @error('phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <flux:label class="text-xs">{{ __('site.email') }}</flux:label>
                            <flux:input type="email" wire:model.defer="email" required />
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost">{{ __('site.cancel') }}</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="primary" icon="save">
                            {{ __('site.save_and_continue') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>

    </div>
</div>
