<div class="space-y-6">
    {{-- Session Messages (Success, Warning, Error) --}}
    @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-yellow-700 dark:text-yellow-300">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <x-student-header title="Métodos de Pago" subtitle="Información para realizar tus pagos" icon="credit-card"
        :student="$student" />

    @if (empty($acceptedMethods))
        <div class="bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
            <p class="text-zinc-600 dark:text-zinc-400">
                No hay métodos de pago configurados. Contactate con el gimnasio para más información.
            </p>
        </div>
    @else
        {{-- Estado del pago / Invoice pendiente --}}
        @php
            // Solo mostrar alerta si faltan menos de 5 días para vencer o está vencido
            $daysUntilDue = now()->diffInDays($pendingInvoice?->due_date);
            $showPendingAlert = $pendingInvoice && (
                $pendingInvoice->is_overdue ||
                $daysUntilDue < 5
            );
        @endphp

        @if ($showPendingAlert)
            <x-student.alert-notification type="warning">
                <p class="text-sm"><span class="font-medium">Pago {{ $pendingInvoice->is_overdue ? 'Vencido' : 'Pendiente' }}:</span> {{ $pendingInvoice->formatted_amount }} - Vencimiento: {{ $pendingInvoice->due_date->format('d/m/Y') }}</p>
            </x-student.alert-notification>
        @elseif ($student->currentPlanAssignment)
            {{-- Plan al día: se muestra solo en dashboard, aquí no necesitamos mostrar nada --}}
        @endif

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
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center"
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
                                    <p class="text-sm mt-4" style="color: var(--ftt-color-base);">
                                        <x-icons.lucide.info class="size-4 inline mr-1" />
                                        <span class="underline">{{ $transferConfig['instructions'] }}</span>
                                    </p>
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
                        <div
                            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                                        style="background: linear-gradient(to bottom right, var(--ftt-color-base), var(--ftt-color-base-dark, var(--ftt-color-base)));">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100 mb-4">Efectivo
                                    </h3>
                                    @if ($cashConfig['instructions'])
                                        <p class="text-sm mb-3" style="color: var(--ftt-color-base);">
                                            <x-icons.lucide.info class="size-4 inline mr-1" />
                                            <span class="underline">{!! nl2br(e($cashConfig['instructions'])) !!}</span>
                                        </p>
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
                        <div
                            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <img src="{{ url('images/MP_RGB_HANDSHAKE_color_vertical.svg') }}"
                                        alt="Mercado Pago" class="w-12 h-12 object-contain">
                                </div>

                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100 mb-4">Mercado
                                        Pago</h3>
                                    <p class="text-zinc-600 dark:text-zinc-400 mb-4">Pagá de forma rápida y segura con
                                        Mercado
                                        Pago.</p>

                                    @if (!empty($pricing))
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                                            <span class="font-medium text-zinc-800 dark:text-zinc-100">Plan:</span>
                                            {{ $pricing['plan_name'] ?? 'Plan' }}
                                            @if (!empty($pricing['label']))
                                                <span
                                                    class="text-zinc-500 dark:text-zinc-400">({{ $pricing['label'] }})</span>
                                            @endif
                                            <div class="mt-1">
                                                <span
                                                    class="font-medium text-zinc-800 dark:text-zinc-100">Importe:</span>
                                                {{ $pricing['currency'] ?? 'ARS' }}
                                                {{ number_format((float) ($pricing['amount'] ?? 0), 2, ',', '.') }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($paymentError)
                                        <p class="text-sm text-red-600 mb-3">{{ $paymentError }}</p>
                                    @endif

                                    @if ($canPayMercadopago)
                                        <button type="button" wire:click="payWithMercadoPago"
                                            wire:loading.attr="disabled" wire:target="payWithMercadoPago"
                                            class="inline-flex items-center gap-2 px-6 py-3 bg-[#009ee3] hover:bg-[#0088cc] text-white font-medium rounded-xl transition disabled:opacity-60">
                                            <span wire:loading.remove wire:target="payWithMercadoPago">
                                                @if ($pendingInvoice)
                                                    Pagar {{ $pendingInvoice->formatted_amount }}
                                                @else
                                                    Pagar con Mercado Pago
                                                @endif
                                            </span>
                                            <span wire:loading wire:target="payWithMercadoPago">Generando
                                                link...</span>
                                        </button>
                                        @if ($mercadopagoConfig['instructions'])
                                            <p class="text-sm mt-3" style="color: var(--ftt-color-base);">
                                                <x-icons.lucide.info class="size-4 inline mr-1" />
                                                <span
                                                    class="underline">{{ $mercadopagoConfig['instructions'] }}</span>
                                            </p>
                                        @endif
                                    @else
                                        <button type="button"
                                            class="inline-flex items-center gap-2 px-6 py-3 bg-[#009ee3] text-white font-medium rounded-xl opacity-60 cursor-not-allowed"
                                            disabled>
                                            Pagar con Mercado Pago
                                        </button>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                                            No hay pagos pendientes en este momento.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Historial de Invoices --}}
    @if ($invoices->isNotEmpty())
        <div class="-mt-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">Historial de Pagos</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Últimos {{ $invoices->count() }} invoices</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="border-b border-zinc-200 dark:border-zinc-700">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 text-left">
                                Concepto
                            </th>
                            <th class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 text-left">
                                Monto
                            </th>
                            <th class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 text-left">
                                Vencimiento
                            </th>
                            <th class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 text-center">
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($invoices as $invoice)
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors">
                                <td class="px-6 py-4 text-sm text-zinc-800 dark:text-zinc-200">
                                    @if ($invoice->has_plan_assignment)
                                        {{-- Pago de plan --}}
                                        @php
                                            $planName = $invoice->meta['plan_name'] ?? $invoice->planAssignment?->plan?->name ?? $invoice->planAssignment?->name ?? null;
                                        @endphp
                                        @if (!empty($planName))
                                            {{ $planName }}
                                            @if (!empty($invoice->meta['label']))
                                                <br><span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $invoice->meta['label'] }}</span>
                                            @endif
                                        @else
                                            Plan
                                        @endif
                                    @else
                                        {{-- Pago manual --}}
                                        @if (!empty($invoice->meta['label']))
                                            {{ $invoice->meta['label'] }}
                                        @else
                                            Abono
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $invoice->formatted_amount }}
                                </td>
                                <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $invoice->due_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($invoice->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                            Pagado
                                        </span>
                                    @elseif ($invoice->is_overdue)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                            Vencido
                                        </span>
                                    @elseif ($invoice->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                                            Pendiente
                                        </span>
                                    @elseif ($invoice->status === 'cancelled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                            Cancelado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($hasMoreInvoices)
                <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-zinc-700">
                    <a href="{{ route('tenant.student.invoices') }}"
                        class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 underline">
                        Ver historial completo →
                    </a>
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
                button.innerHTML =
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
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

    // Verificar automáticamente el pago cuando retorna de Mercado Pago
    document.addEventListener('DOMContentLoaded', function() {
        // Si hay un invoice pendiente y viene de Mercado Pago, verificar
        @if ($pendingInvoice && $pendingInvoice->external_reference)
            // Esperar un poco para que Livewire esté listo
            setTimeout(() => {
                console.log('Verificando pago en Mercado Pago...');
                // Disparar evento en Livewire
                Livewire.dispatch('paymentCheck');
            }, 1000);
        @endif
    });

    // Escuchar cuando el pago se verifica exitosamente
    window.addEventListener('payment-verified', function(event) {
        if (event.detail?.status === 'paid') {
            // Recargar la página para mostrar el nuevo estado
            console.log('Pago verificado - recargando página');
            setTimeout(() => {
                location.reload();
            }, 2000);
        }
    });

    function openMercadoPagoWindow(url) {
        const width = 700;
        const height = 800;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;

        // Abrir ventana popup de Mercado Pago
        const mp_window = window.open(
            url,
            'MercadoPago',
            `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
        );

        // Verificar cada 500ms si la ventana fue cerrada
        const checkWindow = setInterval(() => {
            try {
                if (mp_window.closed) {
                    clearInterval(checkWindow);
                    console.log('Ventana Mercado Pago cerrada - verificando pago...');
                    // Recargar después de 1 segundo para ver si el pago se procesó
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            } catch (e) {
                // En caso de error, limpiar
                clearInterval(checkWindow);
            }
        }, 500);
    }
</script>
