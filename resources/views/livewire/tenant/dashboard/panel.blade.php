<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">

        {{-- KPIs (estilo ejemplo) --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 flex flex-col">
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
            </div>

            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 flex flex-col">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-neutral-500">{{ __('site.running_routines') }}</p>
                        <p class="mt-1 text-2xl font-semibold">{{ number_format($draftCount) }}</p>
                    </div>
                    <div class="rounded-lg bg-neutral-100 dark:bg-neutral-800 p-2">
                        <flux:icon.dumbbell class="size-5" />
                    </div>
                </div>
                <p class="text-xs text-neutral-500 mt-auto pt-3">{{ __('site.ready_to_publish') }}:
                    <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $readyToPublish }}</span>
                </p>
            </div>

            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 flex flex-col">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-neutral-500">{{ __('site.unread_messages') }}</p>
                        <p class="mt-1 text-2xl font-semibold">{{ number_format($unreadContacts) }}</p>
                    </div>
                    <div class="rounded-lg bg-neutral-100 dark:bg-neutral-800 p-2">
                        <flux:icon.inbox class="size-5" />
                    </div>
                </div>
                <p class="text-xs text-neutral-500 mt-auto pt-3">{{ __('site.contacts_today') }}:
                    <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $contactsToday }}</span>
                </p>
            </div>

            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 flex flex-col">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-neutral-500">{{ __('site.other_metric') }}</p>
                        <p class="mt-1 text-2xl font-semibold">{{ number_format($blogCount) }}</p>
                    </div>
                    <div class="rounded-lg bg-neutral-100 dark:bg-neutral-800 p-2">
                        <flux:icon.activity class="size-5" />
                    </div>
                </div>
                <p class="text-xs text-neutral-500 mt-auto pt-3">{{ __('site.updated') }}: <span
                        class="font-medium text-neutral-700 dark:text-neutral-300">hoy</span></p>
            </div>
        </div>

        {{-- Gráfico + Acciones rápidas (estilo ejemplo) --}}
        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Altas vs Bajas por semana (últimas 12) --}}
            <div
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold">{{ __('site.students_in_out_weekly') }}</h3>
                    <a wire:navigate href="{{ route('tenant.dashboard.students.index') }}"
                        class="text-xs text-[var(--brand-700)] hover:underline">
                        {{ __('site.view_all') }}
                    </a>
                </div>

                <!-- Legend -->
                <div class="flex justify-center sm:justify-end items-center gap-x-4 mb-3 sm:mb-6 mt-2">
                    <div class="inline-flex items-center">
                        <span class="size-2.5 inline-block bg-blue-600 rounded-sm me-2"></span>
                        <span
                            class="text-[13px] text-gray-600 dark:text-neutral-400">{{ __('site.new_students') }}</span>
                    </div>
                    <div class="inline-flex items-center">
                        <span class="size-2.5 inline-block bg-purple-600 rounded-sm me-2"></span>
                        <span class="text-[13px] text-gray-600 dark:text-neutral-400">{{ __('site.churn') }}</span>
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
                        <flux:button class="!h-auto w-full flex flex-col items-center justify-center py-6 text-center"
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

                    {{-- Rutinas --}}
                    <flux:button as="a" wire:navigate href="{# route('tenant.dashboard.routines.index') #}"
                        class="!h-auto flex flex-col items-center justify-center py-6 text-center">
                        <flux:icon.dumbbell class="size-6 mb-2" />
                        <span class="text-sm font-medium">{{ __('site.manage_routines') }}</span>
                    </flux:button>

                    {{-- Mensajes --}}
                    <flux:button as="a" wire:navigate href="{# route('tenant.dashboard.messages.index') #}"
                        class="!h-auto flex flex-col items-center justify-center py-6 text-center">
                        <flux:icon.mail class="size-6 mb-2" />
                        <span class="text-sm font-medium">{{ __('site.review_unread') }}</span>
                    </flux:button>

                    {{-- Pago / ingreso rápido (reserva para futuro) --}}
                    <flux:button as="a" href="#"
                        class="!h-auto flex flex-col items-center justify-center py-6 text-center col-span-2">
                        <flux:icon.wallet class="size-6 mb-2" />
                        <span class="text-sm font-medium">{{ __('site.new_payment') }}</span>
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
                        <flux:button size="sm">{{ __('site.mark_all_as_read') }}</flux:button>
                        <a wire:navigate href="{# route('tenant.dashboard.messages.index') #}"
                            class="text-xs text-[var(--brand-700)] hover:underline">{{ __('site.view_all') }}</a>
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

            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4">
                <h3 class="text-sm font-semibold">{{ __('site.top_packages') }}</h3>
                <ul class="mt-3 space-y-3">
                    <li class="text-neutral-500">{{ __('site.no_packages_yet') }}</li>
                </ul>
            </div>
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
                            <flux:input type="email" wire:model.defer="email" />
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
