<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900">
        @php
            $user = auth()->user();
            $role = $user?->role;
            $isAlumni = $user && $role === 'alumni';
            $showDashboardLink = $user && ! in_array($role, ['staf', 'dekan', 'superadmin'], true);
        @endphp

        @if ($isAlumni)
            <div class="mx-auto w-full max-w-5xl px-6 py-8">
                <header class="flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('pengajuan.index') }}" class="flex items-center gap-3 font-semibold" wire:navigate>
                        <x-app-logo-icon class="h-7 fill-current text-black dark:text-white" />
                        <span>{{ __('E-Legalisir') }}</span>
                    </a>

                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <span class="text-zinc-500 dark:text-zinc-300">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <flux:button as="button" type="submit" variant="ghost" icon="arrow-right-start-on-rectangle">
                                {{ __('Log Out') }}
                            </flux:button>
                        </form>
                    </div>
                </header>

                <main class="mt-8 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                    {{ $slot }}
                </main>
            </div>
        @else
            <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

                <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                    <x-app-logo />
                </a>

                <flux:navlist variant="outline">
                    <flux:navlist.group :heading="__('Platform')" class="grid">
                        @if ($showDashboardLink)
                            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                        @endif

                        @if (in_array(auth()->user()->role, ['staf', 'dekan', 'superadmin'], true))
                            <flux:navlist.item
                                icon="clipboard-document"
                                :href="route('admin.pengajuan.index')"
                                :current="request()->routeIs('admin.pengajuan.*')"
                                wire:navigate
                            >{{ __('Manajemen Pengajuan') }}</flux:navlist.item>
                        @endif

                        @if (auth()->user()->role === 'superadmin')
                            <flux:navlist.item
                                icon="shield-check"
                                :href="route('superadmin.dashboard')"
                                :current="request()->routeIs('superadmin.*')"
                                wire:navigate
                            >{{ __('Dashboard Superadmin') }}</flux:navlist.item>

                            <flux:navlist.item
                                icon="users"
                                :href="route('superadmin.roles')"
                                :current="request()->routeIs('superadmin.roles')"
                                wire:navigate
                            >{{ __('Manajemen Role') }}</flux:navlist.item>
                        @endif
                    </flux:navlist.group>
                </flux:navlist>

                <flux:spacer />

                <flux:navlist variant="outline">
                    <flux:navlist.item icon="globe-alt" :href="route('home')" wire:navigate>
                        {{ __('Landing Page') }}
                    </flux:navlist.item>

                    <flux:navlist.item icon="book-open-text" href="{{ route('home') }}#alumni-section" wire:navigate>
                        {{ __('Panduan Pengajuan') }}
                    </flux:navlist.item>
                </flux:navlist>

                <!-- Desktop User Menu -->
                <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon:trailing="chevrons-up-down"
                    />

                    <flux:menu class="w-[220px]">
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
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
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
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
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
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
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </flux:header>

            {{ $slot }}
        @endif

        @fluxScripts
    </body>
</html>
