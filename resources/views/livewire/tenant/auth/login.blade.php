<div class="flex flex-col gap-6">
    <x-tenant.auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

    <!-- Session Status -->
    <x-tenant.auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            @if (Route::has('tenant.password.request'))
                <flux:link class="absolute end-0 top-0 text-sm tenant-accent" :href="route('tenant.password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full tenant-primary">{{ __('Log in') }}</flux:button>
        </div>
    </form>

    @if (Route::has('tenant.register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 tenant-accent">
            {{ __('Don\'t have an account?') }}
            <flux:link :href="route('tenant.register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>
