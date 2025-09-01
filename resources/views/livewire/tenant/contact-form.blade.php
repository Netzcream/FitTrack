<div>
    @if (session()->has('success'))
        <div x-data="{
            show: true,
            fade: true,
            reset() {
                this.show = true;
                this.fade = true;
                setTimeout(() => this.fade = false, 9000);
                setTimeout(() => this.show = false, 9001);
            }
        }" x-init="reset();
        Livewire.on('success-sent', () => reset());" x-show="show"
            :class="{ 'opacity-0': !fade, 'opacity-100': fade }"
            class="relative bg-green-100 text-green-700 p-3 rounded mb-4 transition-opacity duration-3000">
            <button @click="show = false"
                class="absolute top-1/2 right-4 transform -translate-y-1/2 text-green-700 hover:text-green-900 text-2xl leading-none"
                aria-label="Cerrar">
                &times;
            </button>
            {{ session('success') }}
        </div>
    @endif

    @php
        $color = tenant_config('color_base', '#263d83');
        $hover = tenant_config('color_light', '#fafafa');
    @endphp

    <form wire:submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-1 gap-4">
            @foreach ([['model' => 'name', 'type' => 'text', 'placeholder' => 'Nombre y apellido'], ['model' => 'email', 'type' => 'text', 'placeholder' => 'Email'], ['model' => 'mobile', 'type' => 'text', 'placeholder' => 'Teléfono Celular']] as $field)
                <div class="space-y-2">
                    <input type="{{ $field['type'] }}" placeholder="{{ $field['placeholder'] }}"
                        wire:model.defer="{{ $field['model'] }}"
                        class="w-full border border-gray-300 p-3 rounded focus:outline-none focus:ring-2 transition"
                        style="--tw-ring-color: {{ $color }};">
                    @error($field['model'])
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
            @endforeach

            <div class="space-y-2">
                <textarea placeholder="Consulta" rows="8" wire:model.defer="message"
                    class="w-full border border-gray-300 p-3 rounded focus:outline-none focus:ring-2 transition"
                    style="--tw-ring-color: {{ $color }};"></textarea>
                @error('message')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <button type="submit" class="w-full text-white py-3 rounded font-semibold transition-colors duration-300 cursor-pointer"
            style="background-color: {{ $color }};"
            onmouseover="this.style.backgroundColor='{{ $hover }}';"
            onmouseout="this.style.backgroundColor='{{ $color }}';" wire:loading.attr="disabled">

            <span wire:loading.remove wire:target="submit">ENVIAR</span>
            <span wire:loading wire:target="submit">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>

            </span>
        </button>
    </form>
</div>
