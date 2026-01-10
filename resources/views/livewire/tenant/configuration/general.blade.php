{{-- resources/views/livewire/tenant/configuration/general.blade.php --}}
<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6">
        <form wire:submit.prevent="save" class="space-y-6">

            {{-- Header sticky --}}
            <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
                <div class="flex items-center justify-between gap-4 max-w-2xl">
                    <div>
                        <flux:heading size="xl" level="1">
                            {{ __('tenant.configuration.general.title') }}
                        </flux:heading>
                        <flux:subheading size="lg" class="mb-6">
                            {{ __('tenant.configuration.general.subtitle') }}
                        </flux:subheading>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-tenant.action-message class="me-3" on="updated">
                            {{ __('site.saved') }}
                        </x-tenant.action-message>
                        <flux:button type="submit" size="sm">
                            {{ __('site.save') }}
                        </flux:button>
                    </div>
                </div>
                <flux:separator variant="subtle" class="mt-2" />
            </div>

            {{-- Contenido del formulario --}}
            <div class="max-w-2xl space-y-6 pt-2">

                {{-- Sección: Información Básica --}}
                <div class="space-y-4">
                    <div>
                        <flux:heading size="lg" class="!mb-1">Información Básica</flux:heading>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Configurá el nombre de tu gimnasio y datos de contacto principal.</p>
                    </div>
                    <flux:separator variant="subtle" />

                    {{-- Nombre del sitio --}}
                    <flux:field>
                        <flux:label>Nombre del Gimnasio/Centro</flux:label>
                        <flux:input wire:model.defer="name" type="text" required autofocus autocomplete="name"
                            placeholder="Ej: Fitness Center" />
                        <flux:error name="name" />
                    </flux:field>

                    {{-- WhatsApp --}}
                    <flux:field>
                        <flux:label>WhatsApp de Contacto</flux:label>
                        <flux:input.group>
                            <flux:input.group.prefix>+</flux:input.group.prefix>
                            <flux:input wire:model.defer="whatsapp" placeholder="5491123456789" autocomplete="tel" />
                        </flux:input.group>
                        <flux:description>Para que tus alumnos puedan contactarte. Ej: 5491123456789 (código país + área + número)</flux:description>
                        <flux:error name="whatsapp" />
                    </flux:field>
                </div>

                {{-- Sección de métodos de pago --}}
                <div class="space-y-4 pt-4">
                    <div>
                        <flux:heading size="lg" class="!mb-1">Métodos de Pago</flux:heading>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Configurá cómo tus alumnos pueden pagarte. Habilitá los métodos que aceptás.</p>
                    </div>
                    <flux:separator variant="subtle" />

                    {{-- Transferencia/Depósito --}}
                    <div class="p-5 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-xl">
                        <div class="flex items-start gap-3">
                            <flux:checkbox wire:model.defer="accepts_transfer" />
                            <div class="flex-1">
                                <div class="font-medium">Transferencia/Depósito Bancario</div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-0.5">Aceptá pagos por transferencia o depósito directo a tu cuenta.</p>
                            </div>
                        </div>

                        <div x-show="$wire.accepts_transfer" x-transition class="space-y-3 pt-2">
                            <div class="grid grid-cols-2 gap-3">
                                <flux:field>
                                    <flux:label>Banco</flux:label>
                                    <flux:input wire:model.defer="bank_name" placeholder="Ej: Banco Galicia" />
                                    <flux:error name="bank_name" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Titular (opcional)</flux:label>
                                    <flux:input wire:model.defer="bank_account_holder" placeholder="Ej: Juan Pérez" />
                                    <flux:error name="bank_account_holder" />
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:label>CBU</flux:label>
                                <flux:input wire:model.defer="bank_cbu" placeholder="0000000000000000000000" />
                                <flux:error name="bank_cbu" />
                            </flux:field>

                            <div class="grid grid-cols-2 gap-3">
                                <flux:field>
                                    <flux:label>Alias</flux:label>
                                    <flux:input wire:model.defer="bank_alias" placeholder="MI.ALIAS.BANCO" />
                                    <flux:error name="bank_alias" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>CUIT/CUIL (opcional)</flux:label>
                                    <flux:input wire:model.defer="bank_cuit_cuil" placeholder="20-12345678-9" />
                                    <flux:error name="bank_cuit_cuil" />
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:label>Instrucciones o promociones (opcional)</flux:label>
                                <flux:textarea wire:model.defer="transfer_instructions" rows="2"
                                    placeholder="Ej: 10% de descuento pagando antes del día 5 de cada mes" />
                                <flux:description>Podés agregar descuentos, horarios o instrucciones adicionales.</flux:description>
                                <flux:error name="transfer_instructions" />
                            </flux:field>
                        </div>
                    </div>

                    {{-- Mercadopago --}}
                    <div class="p-5 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-xl">
                        <div class="flex items-start gap-3">
                            <flux:checkbox wire:model.defer="accepts_mercadopago" />
                            <div class="flex-1">
                                <div class="font-medium">Mercadopago</div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-0.5">Aceptá pagos con tarjeta, QR o link de pago a través de Mercadopago.</p>
                            </div>
                        </div>

                        <div x-show="$wire.accepts_mercadopago" x-transition class="space-y-3 pt-2">
                            <flux:field>
                                <flux:label>Access Token</flux:label>
                                <flux:input wire:model.defer="mp_access_token" type="password" placeholder="APP_USR-..." />
                                <flux:description>Token de tu aplicación de Mercadopago para procesar pagos.</flux:description>
                                <flux:error name="mp_access_token" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Public Key (opcional)</flux:label>
                                <flux:input wire:model.defer="mp_public_key" placeholder="APP_USR-..." />
                                <flux:description>Clave pública para integraciones web y checkout.</flux:description>
                                <flux:error name="mp_public_key" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Instrucciones o promociones (opcional)</flux:label>
                                <flux:textarea wire:model.defer="mp_instructions" rows="2"
                                    placeholder="Ej: Pagá en cuotas sin interés con todas las tarjetas" />
                                <flux:description>Información adicional o promociones para pagos con Mercadopago.</flux:description>
                                <flux:error name="mp_instructions" />
                            </flux:field>
                        </div>
                    </div>

                    {{-- Efectivo --}}
                    <div class="p-5 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-600 rounded-xl">
                        <div class="flex items-start gap-3">
                            <flux:checkbox wire:model.defer="accepts_cash" />
                            <div class="flex-1">
                                <div class="font-medium">Efectivo</div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-0.5">Recibí pagos en efectivo directamente en tu gimnasio.</p>
                            </div>
                        </div>

                        <div x-show="$wire.accepts_cash" x-transition class="space-y-3 pt-2">
                            <flux:field>
                                <flux:label>Instrucciones o promociones (opcional)</flux:label>
                                <flux:textarea wire:model.defer="cash_instructions" rows="2"
                                    placeholder="Ej: 10% de descuento pagando antes del día 5 de cada mes" />
                                <flux:description>Podés agregar descuentos u horarios de pago.</flux:description>
                                <flux:error name="cash_instructions" />
                            </flux:field>
                        </div>
                    </div>
                </div>

                {{-- Sección de redes sociales --}}
                <div class="space-y-4 pt-4">
                    <div>
                        <flux:heading size="lg" class="!mb-1">Redes Sociales</flux:heading>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Conectá tus perfiles sociales para que aparezcan en tu sitio web.</p>
                    </div>
                    <flux:separator variant="subtle" />

                    <div class="grid grid-cols-2 gap-4">
                        {{-- Instagram --}}
                        <flux:field>
                            <flux:label>Instagram</flux:label>
                            <flux:input.group>
                                <flux:input.group.prefix>@</flux:input.group.prefix>
                                <flux:input wire:model.defer="instagram" placeholder="usuario" />
                            </flux:input.group>
                            <flux:error name="instagram" />
                        </flux:field>

                        {{-- Facebook --}}
                        <flux:field>
                            <flux:label>Facebook</flux:label>
                            <flux:input.group>
                                <flux:input.group.prefix>fb.com/</flux:input.group.prefix>
                                <flux:input wire:model.defer="facebook" placeholder="pagina" />
                            </flux:input.group>
                            <flux:error name="facebook" />
                        </flux:field>

                        {{-- YouTube --}}
                        <flux:field>
                            <flux:label>YouTube</flux:label>
                            <flux:input.group>
                                <flux:input.group.prefix>@</flux:input.group.prefix>
                                <flux:input wire:model.defer="youtube" placeholder="canal" />
                            </flux:input.group>
                            <flux:error name="youtube" />
                        </flux:field>

                        {{-- TikTok --}}
                        <flux:field>
                            <flux:label>TikTok</flux:label>
                            <flux:input.group>
                                <flux:input.group.prefix>@</flux:input.group.prefix>
                                <flux:input wire:model.defer="tiktok" placeholder="usuario" />
                            </flux:input.group>
                            <flux:error name="tiktok" />
                        </flux:field>

                        {{-- Twitter/X --}}
                        <flux:field>
                            <flux:label>X (Twitter)</flux:label>
                            <flux:input.group>
                                <flux:input.group.prefix>@</flux:input.group.prefix>
                                <flux:input wire:model.defer="twitter" placeholder="usuario" />
                            </flux:input.group>
                            <flux:error name="twitter" />
                        </flux:field>
                    </div>
                </div>

            </div>

            {{-- Footer compacto --}}
            <div class="pt-8 max-w-2xl">
                <flux:separator variant="subtle" class="mb-6" />
                <div class="flex justify-end items-center gap-3 opacity-80">
                    <x-tenant.action-message class="me-3" on="updated">
                        {{ __('site.saved') }}
                    </x-tenant.action-message>
                    <flux:button type="submit" size="sm">
                        {{ __('site.save') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
