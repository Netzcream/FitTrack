<div class="flex flex-col gap-6">
    <!-- Logo centrado -->
    <div class="flex flex-col items-center gap-3 mb-2">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-2xl font-extrabold tracking-wide text-indigo-700 dark:text-indigo-400" wire:navigate>
            <x-application-logo class="h-10 w-10" />
            FitTrack
        </a>
        <div class="text-center">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ __('Forgot password') }}</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ __('Enter your email to receive a password reset link') }}</p>
        </div>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email Address')"
            type="email"
            required
            autofocus
            placeholder="email@example.com"
            viewable
        />

        <flux:button variant="primary" type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600">
            {{ __('Email password reset link') }}
        </flux:button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-slate-600 dark:text-slate-400">
        {{ __('Or, return to') }}
        <flux:link class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium" :href="route('login')" wire:navigate>{{ __('log in') }}</flux:link>
    </div>
</div>
