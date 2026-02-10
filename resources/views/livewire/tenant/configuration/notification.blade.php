{{-- resources/views/livewire/tenant/configuration/notification.blade.php --}}
<div class="flex items-start max-md:flex-col">
    <div class="flex-1 self-stretch w-full max-md:pt-6 space-y-8">
        <form wire:submit.prevent="save" class="space-y-6">

            {{-- Header sticky --}}
            <div class="sticky top-0 z-30 bg-inherit backdrop-blur supports-[backdrop-filter]:bg-inherit/95">
                <div class="flex items-center justify-between gap-4 max-w-md">
                    <div>
                        <flux:heading size="xl" level="1">
                            {{ __('tenant.configuration.notification.title') }}
                        </flux:heading>
                        <flux:subheading size="lg" class="mb-6">
                            {{ __('tenant.configuration.notification.subtitle') }}
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
            <div class="max-w-md flex items-end gap-2 max-sm:flex-col">

                <div class="grow">
                    <flux:input wire:model.defer="contact_email"
                        :label="__('tenant.configuration.notification.contact_email')" type="email" required autofocus
                        autocomplete="email" />
                </div>
                <div class="shrink-0">
                    <flux:button variant="ghost" type="button" wire:click="testContactEmail">
                        {{ __('site.test') ?: 'Probar' }}
                    </flux:button>
                </div>

            </div>

            {{-- Footer compacto --}}
            <div class="pt-6 max-w-md">
                <div class="flex justify-end gap-3 items-center text-sm opacity-80">
                    <x-tenant.action-message class="me-3" on="updated">
                        {{ __('site.saved') }}
                    </x-tenant.action-message>
                    <flux:button type="submit" size="sm">
                        {{ __('site.save') }}
                    </flux:button>
                </div>
            </div>

            <flux:separator variant="subtle" class="mt-2" />
        </form>

        <form wire:submit.prevent="sendPushNotification" class="space-y-5 max-w-2xl">
            <div>
                <flux:heading size="lg">
                    {{ __('tenant.configuration.notification.push.title') }}
                </flux:heading>
                <flux:subheading class="mb-1">
                    {{ __('tenant.configuration.notification.push.subtitle') }}
                </flux:subheading>
                <p class="text-xs text-zinc-600 dark:text-zinc-400">
                    {{ __('tenant.configuration.notification.push.active_devices', ['count' => $active_devices_count]) }}
                </p>
            </div>

            @if ($active_devices_count === 0)
                <div class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                    {{ __('tenant.configuration.notification.push.no_devices') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select wire:model.live="push_target" :label="__('tenant.configuration.notification.push.target')">
                    <option value="all">{{ __('tenant.configuration.notification.push.target_all') }}</option>
                    <option value="device">{{ __('tenant.configuration.notification.push.target_device') }}</option>
                </flux:select>

                @if ($push_target === 'device')
                    <flux:select wire:model.defer="push_device_id" :label="__('tenant.configuration.notification.push.device')"
                        @disabled($active_devices_count === 0)>
                        <option value="">{{ __('tenant.configuration.notification.push.device_placeholder') }}</option>
                        @foreach ($push_devices as $device)
                            <option value="{{ $device['id'] }}">{{ $device['label'] }}</option>
                        @endforeach
                    </flux:select>
                @endif
            </div>

            <flux:input
                wire:model.defer="push_title"
                :label="__('tenant.configuration.notification.push.title_label')"
                maxlength="80"
            />

            <flux:textarea
                wire:model.defer="push_message"
                :label="__('tenant.configuration.notification.push.message')"
                rows="3"
                maxlength="120"
            />

            <flux:error name="push_target" />
            <flux:error name="push_device_id" />
            <flux:error name="push_title" />
            <flux:error name="push_message" />
            <flux:error name="push_send" />

            @if ($push_feedback)
                <div class="rounded-lg border px-3 py-2 text-sm {{ $push_feedback_is_error ? 'border-red-300 bg-red-50 text-red-700 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300' : 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' }}">
                    {{ $push_feedback }}
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button type="submit" @disabled($active_devices_count === 0)>
                    {{ __('tenant.configuration.notification.push.send') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
