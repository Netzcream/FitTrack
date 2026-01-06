<div class="flex flex-col gap-6">
    <!-- Logo centrado -->
    <div class="flex flex-col items-center gap-3 mb-2">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-2xl font-extrabold tracking-wide text-indigo-700 dark:text-indigo-400" wire:navigate>
            <x-application-logo class="h-10 w-10" />
            FitTrack
        </a>
        <div class="text-center">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ __('Log in to your account') }}</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ __('Enter your email and password below to log in') }}</p>
        </div>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

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

            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300" :href="route('password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                {{ __('Log in') }}
            </flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-slate-600 dark:text-slate-400">
            {{ __('Don\'t have an account?') }}
            <flux:link class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium" :href="route('register')" wire:navigate>
                {{ __('Sign up') }}
            </flux:link>
        </div>
    @endif
</div>
