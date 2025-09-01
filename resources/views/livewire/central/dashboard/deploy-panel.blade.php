<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch max-md:pt-6">

        <div class="relative mb-6 w-full">
            <flux:heading size="xl" level="1">{{ __('Deploy') }}</flux:heading>
            <flux:subheading size="lg" class="mb-6">{{ __('Desplegar aplicación en producción') }}
            </flux:subheading>
            <flux:separator variant="subtle" />
        </div>

        <div class="mt-5 w-full max-w-lg">

            <section class="w-full">



                <form wire:submit.prevent="runDeploy" class="my-6 w-full space-y-6">




                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-end">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('Ejecutar Despliegue') }}
                            </flux:button>
                        </div>



                    </div>



                    @if ($output)
                        <div>
                            <flux:label :label="__('Resultado')" />
                            <pre class="bg-gray-900 text-white text-sm p-4 rounded max-h-[400px] overflow-auto">{{ $output }}</pre>
                        </div>
                    @endif


                </form>

            </section>
        </div>
    </div>
</div>
