<div class="flex flex-col gap-6">
    <x-tenant.auth-header
        :title="__('site.login_title')"
        :description="__('site.login_subtitle')"
    />

    <!-- Session Status -->
    <x-tenant.auth-session-status class="text-center" :status="session('status')" />

    @php
        $ssoError = request()->query('sso_error');
        $ssoMessage = match ($ssoError) {
            'user_not_found' => __('site.sso_user_not_found'),
            'email_missing' => __('site.sso_email_missing'),
            'oauth_failed' => __('site.sso_failed'),
            'state_expired' => __('site.sso_expired'),
            default => null,
        };
    @endphp

    @if ($ssoMessage)
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-2 text-center text-sm text-red-700">
            {{ $ssoMessage }}
        </div>
    @endif

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
                <flux:link class="absolute end-0 top-0 text-sm tenant-accent"
                           :href="route('tenant.password.request')" wire:navigate>
                    {{ __('site.forgot_password') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('site.remember_me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full tenant-primary">
                {{ __('site.login_button') }}
            </flux:button>
        </div>
    </form>

    @if (config('services.google.client_id'))
        <div class="text-center text-sm text-zinc-500 -mb-2">
            {{ __('site.login_or') }}
        </div>

        <flux:button
            as="a"
            href="{{ route('tenant.google.redirect', array_filter(['redirect' => request()->query('redirect')])) }}"
            variant="filled"
            class="w-full !bg-white hover:!bg-gray-50 !text-gray-700 !border !border-gray-300 hover:!border-gray-400"
        >
            <img src="{{ config('app.url') }}/images/google.svg" alt="Google" class="w-5 h-5" />
            {{ __('site.login_google') }}
        </flux:button>
    @endif

    @if (Route::has('tenant.register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 tenant-accent">
            {{ __('site.no_account') }}
            <flux:link :href="route('tenant.register')" wire:navigate>
                {{ __('site.sign_up') }}
            </flux:link>
        </div>
    @endif
</div>
