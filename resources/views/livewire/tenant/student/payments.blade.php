<div class="space-y-8">
    <x-student-header
        title="Métodos de Pago"
        subtitle="Información para realizar tus pagos"
        icon="credit-card"
        :student="$student" />

    @if (empty($acceptedMethods))
        <div class="bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
            <p class="text-zinc-600 dark:text-zinc-400">
                No hay métodos de pago configurados. Contactate con el gimnasio para más información.
            </p>
        </div>
    @else
        @php
            $hasTransfer = in_array('transfer', $acceptedMethods) && $transferConfig;
            $hasCash = in_array('cash', $acceptedMethods) && $cashConfig;
            $hasMercadopago = in_array('mercadopago', $acceptedMethods) && $mercadopagoConfig;
            $rightColumnCount = ($hasCash ? 1 : 0) + ($hasMercadopago ? 1 : 0);
        @endphp

        <div class="grid gap-6 {{ $hasTransfer && $rightColumnCount > 0 ? 'lg:grid-cols-2' : 'lg:grid-cols-1' }}">
            {{-- Transferencia bancaria (columna izquierda) --}}
            @if ($hasTransfer)
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div
                                class="w-12 h-12 rounded-xl flex items-center justify-center"
                                style="background: linear-gradient(to bottom right, var(--ftt-color-base), var(--ftt-color-base-dark, var(--ftt-color-base)));">
                                <x-icons.lucide.landmark class="w-6 h-6 text-white" />
                            </div>
                        </div>

                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100 mb-4">Transferencia
                                Bancaria</h3>

                            <div class="space-y-3">
                                @if ($transferConfig['bank_name'])
                                    <div>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Banco</p>
                                        <p class="text-zinc-800 dark:text-zinc-200 font-medium">
                                            {{ $transferConfig['bank_name'] }}</p>
                                    </div>
                                @endif

                                @if ($transferConfig['account_holder'])
                                    <div>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Titular</p>
                                        <p class="text-zinc-800 dark:text-zinc-200 font-medium">
                                            {{ $transferConfig['account_holder'] }}</p>
                                    </div>
                                @endif

                                @if ($transferConfig['cuit_cuil'])
                                    <div>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">CUIT/CUIL</p>
                                        <p class="text-zinc-800 dark:text-zinc-200 font-medium">
                                            {{ $transferConfig['cuit_cuil'] }}</p>
                                    </div>
                                @endif

                                @if ($transferConfig['cbu'])
                                    <div>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">CBU</p>
                                        <div class="flex items-center gap-2">
                                            <p class="text-zinc-800 dark:text-zinc-200 font-mono">
                                                {{ $transferConfig['cbu'] }}</p>
                                            <button type="button"
                                                onclick="copyToClipboard('{{ $transferConfig['cbu'] }}', event)"
                                                class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if ($transferConfig['alias'])
                                    <div>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Alias</p>
                                        <div class="flex items-center gap-2">
                                            <p class="text-zinc-800 dark:text-zinc-200 font-medium">
                                                {{ $transferConfig['alias'] }}</p>
                                            <button type="button"
                                                onclick="copyToClipboard('{{ $transferConfig['alias'] }}', event)"
                                                class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if ($transferConfig['instructions'])
                                    <div
                                        class="mt-4 p-3 rounded-lg border inline-block max-w-full"
                                        style="background-color: var(--ftt-color-base-transparent); border-color: var(--ftt-color-base);">
                                        <p class="text-sm flex items-start gap-2"
                                           style="color: var(--ftt-color-base);">
                                            <x-icons.lucide.badge-percent class="size-4 mt-0.5 flex-shrink-0" />
                                            <span><span class="font-medium">Promoción:</span>
                                                {{ $transferConfig['instructions'] }}</span>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Columna derecha (Efectivo y Mercado Pago) --}}
            @if ($hasCash || $hasMercadopago)
                <div class="space-y-6">
                    {{-- Efectivo --}}
                    @if ($hasCash)
                        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 rounded-xl flex items-center justify-center"
                                        style="background: linear-gradient(to bottom right, var(--ftt-color-base), var(--ftt-color-base-dark, var(--ftt-color-base)));">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100 mb-4">Efectivo</h3>
                                    @if ($cashConfig['instructions'])
                                        <div
                                            class="p-3 rounded-lg border inline-block max-w-full"
                                            style="background-color: var(--ftt-color-base-transparent); border-color: var(--ftt-color-base);">
                                            <p class="text-sm flex items-start gap-2"
                                               style="color: var(--ftt-color-base);">
                                                <x-icons.lucide.info class="size-4 mt-0.5 flex-shrink-0" />
                                                <span>{!! nl2br(e($cashConfig['instructions'])) !!}</span>
                                            </p>
                                        </div>
                                    @else
                                        <p class="text-zinc-600 dark:text-zinc-400">Pagá en efectivo directamente en el
                                            gimnasio.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Mercado Pago --}}
                    @if ($hasMercadopago)
                        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <img src="{{ url('images/MP_RGB_HANDSHAKE_color_vertical.svg') }}" alt="Mercado Pago"
                                        class="w-12 h-12 object-contain">
                                </div>

                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100 mb-4">Mercado Pago</h3>
                                    <p class="text-zinc-600 dark:text-zinc-400 mb-4">Pagá de forma rápida y segura con Mercado
                                        Pago.</p>

                                    <button type="button"
                                        class="inline-flex items-center gap-2 px-6 py-3 bg-[#009ee3] hover:bg-[#0088cc] text-white font-medium rounded-xl transition">
                                        Pagar con Mercado Pago
                                    </button>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">Próximamente disponible</p>

                                    @if ($mercadopagoConfig['instructions'])
                                        <div
                                            class="mt-4 p-3 rounded-lg border inline-block max-w-full"
                                            style="background-color: var(--ftt-color-base-transparent); border-color: var(--ftt-color-base);">
                                            <p class="text-sm flex items-start gap-2"
                                               style="color: var(--ftt-color-base);">
                                                <x-icons.lucide.badge-percent class="size-4 mt-0.5 flex-shrink-0" />
                                                <span><span class="font-medium">Promoción:</span>
                                                    {{ $mercadopagoConfig['instructions'] }}</span>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>

<script>
    function copyToClipboard(text, event) {
        const button = event.currentTarget;
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                // Cambiar ícono temporalmente
                const originalHTML = button.innerHTML;
                button.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                button.classList.add('text-green-600', 'dark:text-green-400');

                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('text-green-600', 'dark:text-green-400');
                }, 1500);
            }
        } catch (err) {
            console.error('Error al copiar:', err);
            alert('No se pudo copiar. Texto: ' + text);
        } finally {
            document.body.removeChild(textarea);
        }
    }
</script>
