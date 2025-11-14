<div>
    <div
        class="w-[min(640px,92vw)] mx-auto rounded-xl border
               border-gray-200 dark:border-white/10
               bg-white/80 dark:bg-white/5
               backdrop-blur-md shadow px-6 py-5">

        @php
            $base = 'w-full px-3 py-2 rounded-md
                bg-gray-50 dark:bg-white/5
                text-gray-900 dark:text-white
                placeholder:text-gray-400 dark:placeholder:text-white/50
                border border-gray-300 dark:border-white/10
                focus:outline-none focus:ring-2 focus:ring-[#3bc9f5] focus:border-transparent';

            $err = 'border-red-500/80 ring-2 ring-red-500/60
                bg-red-50 dark:bg-red-500/5
                placeholder:text-red-400 dark:placeholder:text-red-300';
        @endphp

        <form wire:submit.prevent="save" wire:key="contact-product-form-{{ $formKey }}" class="space-y-2" autocomplete="off">
            {{-- Nombre --}}
            <input type="text" id="pf-name" name="name" wire:model.defer="name"
                @class([$base, $errors->has('name') ? $err : ''])
                placeholder="{{ __('site.name') }} *" />
            @error('name')
                <p id="pf-name-error" class="pb-2 text-left text-xs text-red-500 dark:text-red-300">{{ $message }}</p>
            @enderror

            {{-- Email --}}
            <input type="email" id="pf-email" name="email" wire:model.defer="email"
                @class([$base, $errors->has('email') ? $err : ''])
                placeholder="Email *" />
            @error('email')
                <p id="pf-email-error" class="pb-2 text-left text-xs text-red-500 dark:text-red-300">{{ $message }}</p>
            @enderror

            {{-- Teléfono --}}
            <input type="text" id="pf-phone" name="phone" wire:model.defer="phone"
                @class([$base, $errors->has('phone') ? $err : ''])
                placeholder="{{ __('site.phone') }}" />
            @error('phone')
                <p id="pf-phone-error" class="pb-2 text-left text-xs text-red-500 dark:text-red-300">{{ $message }}</p>
            @enderror

            {{-- Mensaje --}}
            <textarea id="pf-body" name="body" wire:model.defer="body" rows="3"
                @class([$base, 'min-h-[112px]', $errors->has('body') ? $err : ''])
                placeholder="{{ __('site.message') }} *"></textarea>
            @error('body')
                <p id="pf-body-error" class="pb-2 text-left text-xs text-red-500 dark:text-red-300">{{ $message }}</p>
            @enderror

            {{-- Botón enviar --}}
            <button type="button"
                class="block w-full mt-2 uppercase tracking-wide
                       text-gray-900 dark:text-white
                       bg-gray-100 dark:bg-white/5
                       hover:bg-gray-200 dark:hover:bg-white/10
                       border border-gray-300 dark:border-0
                       font-medium focus:ring-4 focus:outline-none transition
                       rounded-lg text-sm px-5 py-2.5"
                wire:click="save" wire:loading.remove wire:target="save">
                {{ __('site.send_contact') }}
            </button>

            {{-- Botón loading --}}
            <button type="button"
                class="w-full mt-2 uppercase tracking-wide
                       text-gray-900 dark:text-white
                       bg-gray-100 dark:bg-white/5
                       border border-gray-300 dark:border-0
                       font-medium focus:ring-4 focus:outline-none transition
                       rounded-lg text-sm px-5 py-2.5
                       flex items-center justify-center gap-2"
                disabled wire:loading.flex wire:target="save"
                aria-live="polite" aria-busy="true">
                <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                {{ __('site.sending') }}
            </button>
        </form>
    </div>

    <x-action-message class="mt-6" on="contact-show-saved">
        {{ __('site.contact_sent') }}
    </x-action-message>
</div>
