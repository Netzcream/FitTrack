<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">

        {{-- Header sticky --}}
        <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
            <div class="flex items-center justify-between gap-4 max-w-5xl">
                <div class="flex items-center gap-3">
                    {{-- Avatar --}}
                    <div class="h-10 w-10 rounded-full overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center">
                        @if ($conversation->student?->hasMedia('avatar'))
                            <img src="{{ $conversation->student->getFirstMediaUrl('avatar', 'thumb') }}"
                                 alt="{{ $conversation->student->full_name }}"
                                 class="object-cover h-full w-full">
                        @else
                            <span class="text-xs font-semibold">
                                {{ strtoupper(substr($conversation->student?->first_name ?? 'S',0,1).substr($conversation->student?->last_name ?? 'N',0,1)) }}
                            </span>
                        @endif
                    </div>

                    <div>
                        <flux:heading size="xl" level="1">{{ $conversation->student?->full_name ?? __('Sin alumno') }}</flux:heading>
                        <flux:subheading size="lg" class="mb-6">{{ $conversation->student?->email }}</flux:subheading>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <flux:button as="a" variant="ghost" href="{{ route('tenant.dashboard.messages.conversations.index') }}" size="sm" icon="arrow-left">
                        {{ __('Volver') }}
                    </flux:button>
                </div>
            </div>
            <flux:separator variant="subtle" class="mt-2" />
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
                                <div class="max-w-[70%] {{ $message->sender_type->value === 'tenant' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100' }} rounded-lg px-4 py-2 shadow-sm">
                                    <div class="text-sm break-words whitespace-pre-wrap">{{ $message->body }}</div>
                                    <div class="text-xs {{ $message->sender_type->value === 'tenant' ? 'text-blue-100' : 'text-gray-500 dark:text-neutral-400' }} mt-1">
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
                                    class="p-3 rounded-full bg-blue-500 hover:bg-blue-600 text-white transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
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
