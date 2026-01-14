<div class="space-y-6">
    <x-student-header
        title="Historial de Pagos"
        subtitle="Tus invoices y pagos realizados"
        icon="receipt"
        :student="$student" />

    @if ($invoices->isEmpty())
        <div class="bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-zinc-400 dark:text-zinc-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-zinc-600 dark:text-zinc-400">No tenés invoices aún</p>
        </div>
    @else
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-700">
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Concepto</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Vencimiento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($invoices as $invoice)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition">
                                <td class="px-6 py-4 text-zinc-700 dark:text-zinc-300">
                                    {{ $invoice->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-zinc-700 dark:text-zinc-300">
                                    @if (!empty($invoice->meta['label']))
                                        {{ $invoice->meta['label'] }}
                                    @elseif (!empty($invoice->meta['plan_name']))
                                        Plan {{ $invoice->meta['plan_name'] }}
                                    @else
                                        Abono
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $invoice->formatted_amount }}
                                </td>
                                <td class="px-6 py-4 text-zinc-700 dark:text-zinc-300">
                                    {{ $invoice->due_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    @switch($invoice->status)
                                        @case('paid')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                Pagado
                                            </span>
                                        @break

                                        @case('overdue')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                                Vencido
                                            </span>
                                        @break

                                        @case('pending')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                </svg>
                                                Pendiente
                                            </span>
                                        @break

                                        @case('cancelled')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-300">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                                Cancelado
                                            </span>
                                        @break
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginación --}}
        @if ($invoices->hasPages())
            <div class="mt-6">
                {{ $invoices->links('components.preline.pagination') }}
            </div>
        @endif
    @endif
</div>
