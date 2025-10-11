<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">💳 Pagos</h1>
        <p class="text-gray-500">Gestioná tus abonos y comprobantes</p>
    </div>

    {{-- Estado actual --}}
    @if ($pendingPayment)
        <div class="bg-white shadow rounded-xl p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-700">Próximo vencimiento</h2>

            <p class="text-gray-600">
                Monto a pagar: <span
                    class="font-semibold text-gray-900">${{ number_format($pendingPayment->amount, 2) }}</span>
            </p>
            <p class="text-sm text-gray-500">Método: {{ ucfirst($pendingPayment->method) }}</p>

            <div class="flex flex-col md:flex-row gap-4 mt-4">
                {{-- Pago por MercadoPago --}}
                <a href="{{ $pendingPayment->transaction_id ? $pendingPayment->transaction_id : '#' }}" target="_blank"
                    class="flex-1 bg-[#00AEEF] hover:bg-[#009ed9] text-white font-medium text-center py-3 rounded-xl transition disabled:opacity-50 {{ $pendingPayment->transaction_id ? '' : 'pointer-events-none opacity-60' }}">
                    Pagar con MercadoPago
                </a>

                {{-- Subida de comprobante --}}
                <div class="flex-1 bg-gray-50 border rounded-xl p-4">
                    <p class="text-sm text-gray-600 mb-2">Pago por transferencia</p>

                    <ul class="text-sm text-gray-700 mb-3">
                        <li><strong>Banco:</strong> Banco Nación</li>
                        <li><strong>Alias:</strong> fittrack.empresa</li>
                        <li><strong>CBU:</strong> 1234567890123456789012</li>
                    </ul>

                    @if (!$uploadSuccess && !$pendingPayment->proof_url)
                        <form wire:submit.prevent="uploadProof" class="space-y-3">
                            <input type="file" wire:model="proof" accept=".jpg,.jpeg,.png,.pdf"
                                class="block w-full text-sm text-gray-700">
                            <button type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-lg">
                                📤 Enviar comprobante
                            </button>
                        </form>
                        @error('proof')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    @else
                        <div class="text-sm text-gray-700">
                            <p class="text-green-600 font-medium mb-1">✅ Comprobante subido</p>
                            @if ($pendingPayment->proof_url)
                                <a href="{{ $pendingPayment->proof_url }}" target="_blank"
                                    class="underline text-blue-600">
                                    Ver archivo
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>
    @else
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
            <p class="text-green-800">
                🎉 No tenés pagos pendientes en este momento.
            </p>
        </div>
    @endif

    {{-- Historial --}}
    <div class="bg-white shadow rounded-xl p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-3">Historial de pagos</h2>

        <livewire:tenant.student.payments-history />
    </div>
</div>
