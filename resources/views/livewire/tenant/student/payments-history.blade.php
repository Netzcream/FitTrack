<div>
    @if ($payments->count())
        <table class="w-full text-sm text-left text-gray-600">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 font-semibold">Fecha</th>
                    <th class="p-2 font-semibold">Monto</th>
                    <th class="p-2 font-semibold">Método</th>
                    <th class="p-2 font-semibold">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $p)
                    <tr class="border-b">
                        <td class="p-2">{{ optional($p->paid_at ?? $p->created_at)->format('d/m/Y') }}</td>
                        <td class="p-2">${{ number_format($p->amount, 2) }}</td>
                        <td class="p-2 capitalize">{{ $p->method }}</td>
                        <td class="p-2">
                            @switch($p->status)
                                @case('confirmed')
                                    <span class="text-green-600 font-medium">Confirmado</span>
                                @break

                                @case('under_review')
                                    <span class="text-yellow-600 font-medium">En revisión</span>
                                @break

                                @case('rejected')
                                    <span class="text-red-600 font-medium">Rechazado</span>
                                @break

                                @default
                                    <span class="text-gray-600">Pendiente</span>
                            @endswitch
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $payments->links() }}</div>
    @else
        <p class="text-sm text-gray-500">No hay pagos registrados aún.</p>
    @endif
</div>
