<div class="flex flex-col gap-6">
    <x-tenant.auth-header
        :title="__('site.login_title')"
        :description="__('site.login_subtitle')"
    />

    <!-- Session Status -->
    <x-tenant.auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('site.email_label')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="{{ __('site.email_placeholder') }}"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('site.password_label')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('site.password_placeholder')"
                viewable
            />

            @if (Route::has('tenant.password.request'))
                <flux:link class="absolute end-0 top-0 text-sm"
                           :href="route('tenant.password.request')" wire:navigate>
                    {{ __('site.forgot_password') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('site.remember_me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('site.login_button') }}
            </flux:button>
        </div>
    </form>

    @if (Route::has('tenant.register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('site.no_account') }}
            <flux:link :href="route('tenant.register')" wire:navigate>
                {{ __('site.sign_up') }}
            </flux:link>
        </div>
    @endif
</div>
