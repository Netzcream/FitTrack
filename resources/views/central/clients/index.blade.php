<x-layouts.app :title="__('central.clients')">



    <div class="flex items-start max-md:flex-col">
        <div class="flex-1 self-stretch max-md:pt-6">

            <div class="relative mb-6 w-full">

                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <flux:heading size="xl" level="1">{{ __('central.clients') }}</flux:heading>
                        <flux:subheading size="lg">{{ __('central.list_of_clients') }}</flux:subheading>
                    </div>

                    <flux:button
                        as="a"
                        href="{{ route('central.dashboard.clients.create') }}"
                        variant="primary"
                        icon="plus"
                    >
                        {{ __('site.new') }}
                    </flux:button>
                </div>



                <flux:separator variant="subtle" />

            </div>

            <div class="mt-5 w-full ">

                <section class="w-full">


                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Dominio
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    DDBB
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    SSL
                                </th>
                                <th scope="col" class="px-6 py-3  text-right">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tenants as $tenant)
                                <tr
                                    class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                    <th scope="row"
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $tenant->name }}</td>
                                    <td class="px-6 py-4">

                                        <a href="//{{ $tenant->domains->first()->domain ?? '' }}" target="_blank"
                                            class="font-medium text-blue-600 dark:text-blue-500 hover:underline">{{ $tenant->domains->first()->domain ?? '' }}</a>


                                    </td>
                                    <td class="px-6 py-4">{{ $tenant->tenancy_db_name }}</td>
                                    <td class="px-6 py-4">
                                        {{ $tenant->status->label() }}
                                    </td>
                                    <td>

                                        @php
                                            $domain = $c->domains?->first()?->domain;
                                            //$domain = "lunigo.com";
                                        @endphp
                                        @if (\App\Models\Tenant::hasValidSslFor($domain))
                                            ✅ Válido hasta
                                            {{ \App\Models\Tenant::sslExpirationDateFor($domain)?->format('Y-m-d') }}
                                        @else
                                            ❌ Vencido o no instalado
                                        @endif


                                    </td>
                                    <td class="px-6 py-4 text-right">

                                        {{--
                                        <flux:button as="a"
                                            href="{{ route('central.dashboard.clients.show', $tenant->id) }}"
                                            variant="primary" size="sm">
                                            Ver
                                        </flux:button>
                                        --}}

                                        <flux:button as="a"
                                            href="{{ route('central.dashboard.clients.edit', $tenant) }}"
                                            variant="primary" size="sm">
                                            Editar
                                        </flux:button>

                                        @if ($c->status != App\Enums\TenantStatus::DELETED)
                                            <form
                                                action="{{ route('central.dashboard.clients.destroy', ['client' => $tenant->id]) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <flux:button type="submit" variant="danger" size="sm">
                                                    Eliminar
                                                </flux:button>
                                            </form>
                                        @else
                                            <form
                                                action="{{ route('central.dashboard.clients.force', ['client' => $tenant->id]) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <flux:button type="submit" variant="danger" size="sm">
                                                    Eliminar!!!
                                                </flux:button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach



                        </tbody>
                    </table>


                </section>
            </div>
        </div>
    </div>


</x-layouts.app>
