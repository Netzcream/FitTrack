@php
    use Illuminate\Support\Str;
@endphp

<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">

        {{-- Header sticky --}}
        <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
            <div class="flex items-center justify-between gap-4 max-w-5xl">
                <div class="flex items-center gap-4">
                    {{-- Avatar --}}
                    <div class="h-14 w-14 rounded-full overflow-hidden border-2 border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center flex-shrink-0">
                        @if ($conversation->student?->hasMedia('avatar'))
                            <img src="{{ $conversation->student->getFirstMediaUrl('avatar', 'thumb') }}"
                                 alt="{{ $conversation->student->full_name }}"
                                 class="object-cover h-full w-full">
                        @else
                            <span class="text-base font-semibold">
                                {{ strtoupper(substr($conversation->student?->first_name ?? 'S',0,1).substr($conversation->student?->last_name ?? 'N',0,1)) }}
                            </span>
                        @endif
                    </div>

                    <div class="leading-tight">
                        <div class="text-xl font-semibold text-gray-900 dark:text-neutral-100">
                            {{ $conversation->student?->full_name ?? __('Sin alumno') }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-neutral-400 mt-0.5">
                            {{ $conversation->student?->email }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <flux:button as="a" variant="ghost" href="{{ route('tenant.dashboard.messages.conversations.index') }}" size="sm" icon="arrow-left">
                        {{ __('Volver') }}
                    </flux:button>
                </div>
            </div>
            <flux:separator variant="subtle" class="mt-3" />
        </div>

        {{-- Messages container --}}
        <div class="max-w-5xl mt-6">
            <div class="bg-gray-50 dark:bg-neutral-900/50 border border-gray-200 dark:border-neutral-700 rounded-lg overflow-hidden">

                {{-- Messages list --}}
                <div class="h-[500px] overflow-y-auto p-4 space-y-4"
                     x-data="{
                         shouldScroll: true,
                         messageCount: {{ $messages->count() }},
                         scrollIfNeeded() {
                             const container = document.getElementById('messages-container');
                             if (container) {
                                 const newCount = container.children.length;
                                 if (newCount > this.messageCount && this.shouldScroll) {
                                     this.messageCount = newCount;
                                     $nextTick(() => { $el.scrollTop = $el.scrollHeight });
                                 }
                             }
                         }
                     }"
                     x-init="$el.scrollTop = $el.scrollHeight; setInterval(() => scrollIfNeeded(), 3100)"
                     @scroll="shouldScroll = Math.abs($el.scrollHeight - $el.scrollTop - $el.clientHeight) < 50"
                     @message-sent.window="setTimeout(() => { $el.scrollTop = $el.scrollHeight }, 100)">

                    <div wire:poll.3s class="space-y-4" id="messages-container">
                        @forelse ($messages as $message)
                            <div class="flex {{ $message->sender_type->value === 'tenant' ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[70%] {{ $message->sender_type->value === 'tenant' ? 'text-white' : 'bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100' }} rounded-lg px-4 py-2 shadow-sm"
                                     @if($message->sender_type->value === 'tenant') style="background-color: var(--ftt-color-base);" @endif>
                                    <div class="text-sm break-words whitespace-pre-wrap">{{ $message->body }}</div>
                                    <div class="text-xs {{ $message->sender_type->value === 'tenant' ? 'text-white/80' : 'text-gray-500 dark:text-neutral-400' }} mt-1">
                                        {{ $message->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-center justify-center h-full text-gray-500 dark:text-neutral-400">
                                <div class="text-center">
                                    <x-icons.lucide.message-circle class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-neutral-600" />
                                    <p class="text-sm">{{ __('No hay mensajes aún') }}</p>
                                    <p class="text-xs">{{ __('Envía el primer mensaje para iniciar la conversación') }}</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Message input --}}
                <div class="border-t border-gray-200 dark:border-neutral-700 p-4 bg-white dark:bg-neutral-800" wire:ignore>
                    <form wire:submit.prevent="sendMessage" x-data="messageInput()">
                        <div class="flex gap-3 items-center">
                            <div class="flex-1">
                                <flux:textarea
                                    wire:model.defer="newMessage"
                                    rows="2"
                                    placeholder="{{ __('Escribe tu mensaje...') }}"
                                    class="resize-none"
                                    @keydown.enter="handleKeyDown($event)" />
                            </div>
                            <button type="submit"
                                    class="p-3 rounded-full text-white transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                                    style="background-color: var(--ftt-color-base);"
                                    onmouseover="this.style.backgroundColor = 'var(--ftt-color-dark)'"
                                    onmouseout="this.style.backgroundColor = 'var(--ftt-color-base)'"
                                    x-bind:disabled="$wire.newMessage.trim() === ''">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                    <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-neutral-400 mt-2">{{ __('Enter para enviar, Shift+Enter para nueva línea') }}</p>
                    </form>
                </div>
            </div>
        </div>

        {{-- Panel informativo del alumno --}}
        @if ($conversation->student)
            <div class="max-w-5xl mt-6" wire:ignore>
                <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-neutral-100 flex items-center gap-2">
                            <x-icons.lucide.user-round class="w-5 h-5 text-gray-400 dark:text-neutral-500" />
                            Información del alumno
                        </h3>
                        <a href="{{ route('tenant.dashboard.students.edit', $conversation->student) }}"
                           target="_blank"
                           class="flex items-center gap-1.5 text-sm font-medium hover:underline"
                           style="color: var(--ftt-color-base)">
                            Ver perfil completo
                            <x-icons.lucide.external-link class="size-4" />
                        </a>
                    </div>

                    {{-- Datos personales en una línea --}}
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-3 mb-6 pb-6 border-b border-gray-200 dark:border-neutral-700">
                        @if ($conversation->student->phone)
                            <div class="flex items-center gap-2">
                                <x-icons.lucide.phone class="size-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" />
                                <span class="text-sm text-gray-900 dark:text-neutral-100">{{ $conversation->student->phone }}</span>
                            </div>
                        @endif

                        @php
                            $birthDate = is_array($conversation->student->data)
                                ? ($conversation->student->data['birth_date'] ?? null)
                                : null;
                        @endphp

                        @if ($birthDate)
                            <div class="flex items-center gap-2">
                                <x-icons.lucide.cake class="size-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" />
                                <span class="text-sm text-gray-900 dark:text-neutral-100">
                                    {{ \Carbon\Carbon::parse($birthDate)->format('d/m/Y') }}
                                    ({{ \Carbon\Carbon::parse($birthDate)->age }} años)
                                </span>
                            </div>
                        @endif

                        @if ($conversation->student->goal)
                            <div class="flex items-center gap-2">
                                <x-icons.lucide.target class="size-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" />
                                <span class="text-sm text-gray-900 dark:text-neutral-100">{{ $conversation->student->goal }}</span>
                            </div>
                        @endif

                        @if ($conversation->student->commercialPlan)
                            <div class="flex items-center gap-2">
                                <x-icons.lucide.credit-card class="size-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" />
                                <span class="text-sm text-gray-900 dark:text-neutral-100">{{ $conversation->student->commercialPlan->name }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Plan de entrenamiento debajo --}}
                    @if ($conversation->student->currentPlanAssignment?->plan)
                        @php
                            $plan = $conversation->student->currentPlanAssignment->plan;
                            $exercises = $plan->exercises;
                            $totalExercises = $exercises->count();
                            $exercisesByDay = $exercises->groupBy('day')->sortKeys();
                        @endphp

                        <div class="space-y-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-neutral-300 uppercase tracking-wide">Plan de entrenamiento</h4>

                            <div class="flex flex-wrap items-center gap-x-6 gap-y-3 mb-4">
                                <div class="flex items-center gap-2">
                                    <x-icons.lucide.file-text class="w-4 h-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" />
                                    <span class="font-medium text-gray-900 dark:text-neutral-100">{{ $plan->name }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-icons.lucide.play class="size-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" />
                                    <span class="text-sm text-gray-900 dark:text-neutral-100">
                                        {{ $conversation->student->currentPlanAssignment->starts_at->format('d/m/Y') }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-icons.lucide.hash class="size-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" />
                                    <span class="text-sm text-gray-900 dark:text-neutral-100">{{ $exercisesByDay->count() }} días</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-icons.lucide.dumbbell class="w-4 h-4 text-gray-400 dark:text-neutral-500 flex-shrink-0" />
                                    <span class="text-sm text-gray-900 dark:text-neutral-100">{{ $totalExercises }} ejercicios</span>
                                </div>
                            </div>

                            {{-- Detalle de ejercicios por día --}}
                            <div>
                                <h5 class="text-xs font-semibold text-gray-700 dark:text-neutral-300 uppercase tracking-wide mb-3">Ejercicios por día</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-80 overflow-y-auto">
                                    @foreach ($exercisesByDay as $day => $dayExercises)
                                        <div class="border border-gray-200 dark:border-neutral-700 rounded-lg p-3">
                                            <div class="flex items-center gap-2 mb-2">
                                                <x-icons.lucide.list class="size-4 text-gray-400 dark:text-neutral-500" />
                                                <span class="text-sm font-semibold text-gray-900 dark:text-neutral-100">Día {{ $day }}</span>
                                                <span class="text-xs text-gray-500 dark:text-neutral-400">({{ $dayExercises->count() }} ejercicios)</span>
                                            </div>
                                            <div class="space-y-1.5 ml-6">
                                                @foreach ($dayExercises as $exercise)
                                                    <div class="flex items-start gap-2">
                                                        <x-icons.lucide.circle-dot class="size-3 text-gray-400 dark:text-neutral-500 mt-0.5 flex-shrink-0" />
                                                        <div class="text-sm text-gray-700 dark:text-neutral-300">
                                                            {{ $exercise['name'] ?? 'Ejercicio sin nombre' }}
                                                            @if (!empty($exercise['detail']))
                                                                <span class="text-xs text-gray-500 dark:text-neutral-400 block">{{ $exercise['detail'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-sm text-gray-500 dark:text-neutral-400 italic">
                            Sin plan de entrenamiento asignado
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</div>

<script>
    function messageInput() {
        return {
            handleKeyDown(event) {
                // Si es Enter sin Shift, enviar el formulario
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    this.$el.closest('form').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                }
                // Si es Shift+Enter, permite el comportamiento por defecto (nueva línea)
            }
        }
    }
</script>
