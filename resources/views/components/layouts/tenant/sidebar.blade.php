<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('tenant.partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('tenant.dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse"
            wire:navigate>
            <x-tenant.app-favicon-sidebar />
        </a>



        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('site.platform')" class="grid">
                <flux:navlist.item icon="home" :href="route('tenant.dashboard')"
                    :current="request()->routeIs('tenant.dashboard')" wire:navigate>
                    {{ __('site.dashboard') }}
                </flux:navlist.item>

                @can('gestionar recursos')
                    <flux:navlist.item icon="cog" href="{{ route('tenant.dashboard.landing') }}"
                        :current="request()->routeIs('tenant.dashboard.landing.*')" wire:navigate>
                        {{ __('site.landing') }}
                    </flux:navlist.item>
                @endcan

                @livewire(\App\Livewire\Tenant\ConversationBadgeNavItem::class)


            </flux:navlist.group>

            <flux:navlist variant="outline">
                @livewire(\App\Livewire\Tenant\SupportBadgeNavItem::class)
            </flux:navlist>

            @php
                $configPatterns = ['tenant.dashboard.configuration.*'];
            @endphp

            <flux:navlist.group :heading="__('site.configuration')" expandable
                :expanded="request()->routeIs(...$configPatterns)">

                @can('gestionar recursos')
                    <flux:navlist.item href="{{ route('tenant.dashboard.configuration.general') }}"
                        :current="request()->routeIs('tenant.dashboard.configuration.general')" wire:navigate>
                        {{ __('tenant.configuration.general.title') }}
                    </flux:navlist.item>
                    <flux:navlist.item href="{{ route('tenant.dashboard.configuration.notifications') }}"
                        :current="request()->routeIs('tenant.dashboard.configuration.notifications')" wire:navigate>
                        {{ __('tenant.configuration.notification.title') }}
                    </flux:navlist.item>
                    <flux:navlist.item href="{{ route('tenant.dashboard.configuration.appearance') }}"
                        :current="request()->routeIs('tenant.dashboard.configuration.appearance')" wire:navigate>
                        {{ __('tenant.configuration.appearance.title') }}
                    </flux:navlist.item>
                @endcan


            </flux:navlist.group>



            @php
                $businessPatterns = [
                    'tenant.dashboard.students.*',
                    'tenant.dashboard.exercises.*',
                    'tenant.dashboard.training-plans.*',
                    'tenant.dashboard.messages.*',
                ];
            @endphp
            <flux:navlist.group :heading="__('site.training')" expandable
                :expanded="request()->routeIs(...$businessPatterns)">
                <flux:navlist.item href="{{ route('tenant.dashboard.students.index') }}"
                    :current="request()->routeIs('tenant.dashboard.students.*')" wire:navigate>
                    {{ __('site.students') }}
                </flux:navlist.item>
                <flux:navlist.item href="{{ route('tenant.dashboard.exercises.index') }}"
                    :current="request()->routeIs('tenant.dashboard.exercises.*')" wire:navigate>
                    {{ __('site.exercises') }}
                </flux:navlist.item>
                <flux:navlist.item href="{{ route('tenant.dashboard.training-plans.index') }}"
                    :current="request()->routeIs('tenant.dashboard.training-plans.*')" wire:navigate>
                    {{ __('site.training_plans') }}
                </flux:navlist.item>




            </flux:navlist.group>


            @php
                $businessPatterns = ['tenant.dashboard.commercial-plans.*'];
            @endphp
            <flux:navlist.group :heading="__('site.business_setup')" expandable
                :expanded="request()->routeIs(...$businessPatterns)">
                <flux:navlist.item href="{{ route('tenant.dashboard.commercial-plans.index') }}"
                    :current="request()->routeIs('tenant.dashboard.commercial-plans.*')" wire:navigate>
                    {{ __('site.commercial_plans') }}
                </flux:navlist.item>
            </flux:navlist.group>


        </flux:navlist>

        <flux:navlist variant="outline">
            @canany(['gestionar contactos', 'gestionar usuarios', 'gestionar roles'])
                <flux:navlist.group :heading="__('site.management')">
                    @can('gestionar contactos')
                        <flux:navlist.item icon="envelope" :href="route('tenant.dashboard.contacts.index')"
                            :current="request()->routeIs('tenant.dashboard.contacts.*')" wire:navigate>
                            {{ __('Contactos') }}
                        </flux:navlist.item>
                    @endcan

                    @can('gestionar roles')
                        <flux:navlist.item icon="user" :href="route('tenant.dashboard.roles.index')"
                            :current="request()->routeIs('tenant.dashboard.roles.*')" wire:navigate>
                            {{ __('roles.index_title') }}
                        </flux:navlist.item>
                    @endcan

                    @can('gestionar usuarios')
                        <flux:navlist.item icon="users" :href="route('tenant.dashboard.users.index')"
                            :current="request()->routeIs('tenant.dashboard.users.*')" wire:navigate>
                            {{ __('Usuarios') }}
                        </flux:navlist.item>
                    @endcan
                </flux:navlist.group>
            @endcanany
        </flux:navlist>



        <flux:spacer />

        <flux:dropdown position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon-trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('tenant.dashboard.settings.profile')" icon="cog" wire:navigate>
                        {{ __('Profile') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('tenant.logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('tenant.dashboard.settings.profile')" icon="cog" wire:navigate>
                        {{ __('Profile') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('tenant.logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
