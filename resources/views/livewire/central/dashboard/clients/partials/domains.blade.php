@if ($this->client)
    <div class="mt-6">
        <flux:heading size="lg">{{ __('Dominios del cliente') }}</flux:heading>
        <flux:subheading size="md" class="mt-1">
            {{ __('Podés agregar dominios propios del cliente (apex y/o www). El subdominio principal no puede eliminarse.') }}
        </flux:subheading>

        {{-- Listado --}}
        <div class="mt-4 overflow-hidden border rounded-lg dark:border-neutral-500">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead>
                    <tr>
                        <th
                            class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                            {{ __('Dominio') }}
                        </th>
                        <th class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                            {{ __('Acciones') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                    @php
                        $main = $this->client->id . '.' . env('APP_DOMAIN', 'luniqo.com');
                    @endphp

                    @forelse ($domains as $d)
                        <tr>
                            <td class="px-6 py-3 text-sm text-zinc-800 dark:text-neutral-200">
                                {{ $d['domain'] }}

                                @if (\App\Models\Tenant::hasValidSslFor($d['domain']))
                                    <span>✅ SSL</span>
                                @else
                                    <span>❌ SSL</span>
                                @endif

                                @if ($d['domain'] === $main)
                                    <span
                                        class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ __('Principal') }}
                                    </span>
                                @endif

                            </td>
                            <td class="px-6 py-3 text-sm text-right">
                                @if ($d['domain'] !== $main)
                                    <flux:button wire:click="removeDomain({{ $d['id'] }})" variant="outline"
                                        size="sm">
                                        {{ __('Eliminar') }}
                                    </flux:button>
                                @else
                                    <span class="text-zinc-400 text-xs">{{ __('No editable') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-6 py-3 text-sm text-zinc-500" colspan="2">
                                {{ __('Sin dominios configurados.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Form agregar dominio --}}
        <div class="mt-4 flex items-end gap-3">
            <div class="flex-1">
                <flux:label for="new_domain">{{ __('Agregar dominio') }}</flux:label>
                <flux:input class="mt-2" id="new_domain" wire:model.defer="new_domain" type="text"
                    placeholder="pepe.com.ar o www.pepe.com.ar" />
                @error('new_domain')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="pb-1">
                <flux:button type="button" wire:click="addDomain" variant="primary" icon="plus">
                    {{ __('Agregar') }}
                </flux:button>
            </div>
        </div>
    </div>
@endif
