<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    <tallstackui:script />
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100">
    <x-layout>
        {{-- header --}}
        <x-slot:header>
            <x-layout.header>
                <x-slot:right>
                    <x-dropdown text="Hello, {{ auth()->user()->name }}!">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown.items text="Logout"
                                onclick="event.preventDefault(); this.closest('form').submit();" />
                        </form>
                    </x-dropdown>
                </x-slot:right>
            </x-layout.header>
        </x-slot:header>
        {{-- menu --}}
        <x-slot:menu>
            <x-side-bar smart navigate thin-scroll>
                <x-slot:brand>
                    <div class="flex flex-col items-center justify-center sm:pt-12 pt-6">
                        <img src="{{ asset('ocd-avatar.svg') }}" alt="Avatar" class="w-15 rounded-full" />
                        <span class="w-[88px]:hidden">
                            <x-side-bar.separator text="Office of Civil Defense" line-right />
                        </span>
                    </div>
                </x-slot:brand>
                <x-side-bar.item text="Dashboard">
                    <x-side-bar.item text="Dashboard" icon="chart-bar-square" wire:navigate :current="request()->routeIs('dashboard')"
                        :href="route('dashboard')" />
                </x-side-bar.item>
                <x-side-bar.item text="GASU" :visible="true">
                    <x-side-bar.item text="Supply" icon="cube" wire:navigate :current="request()->routeIs('supply.index')" :href="route('supply.index')" />
                    <x-side-bar.item text="Stock" icon="hashtag" wire:navigate :current="request()->routeIs('stock.index')" :href="route('stock.index')" />
                    <x-side-bar.item text="Requisition" icon="clipboard-document-list" :current="request()->routeIs('requisition.index')" wire:navigate
                        :href="route('requisition.index')" />
                </x-side-bar.item>
                <x-side-bar.item text="PMU" :visible="true">
                    <x-side-bar.item text="Archives" icon="archive-box" wire:navigate :current="request()->routeIs('pmu.index')"
                        :href="route('pmu.index')" />
                </x-side-bar.item>
                <x-side-bar.item text="HRMU" :visible="true">
                    <x-side-bar.item text="Users" icon="users" wire:navigate :current="request()->routeIs('user.index')"
                        :href="route('user.index')" />
                    <x-side-bar.item text="Rectification" icon="clock" wire:navigate :current="request()->routeIs('Rectification')"
                        :href="route('Rectification')" />
                        <x-side-bar.item text="Manage DTR" icon="clock" wire:navigate :current="request()->routeIs('Managedtr')"
                        :href="route('Managedtr')" />
                </x-side-bar.item>

            </x-side-bar>
        </x-slot:menu>

        <div class="min-h-screen font-poppins">{{ $slot }}</div>
    </x-layout>

    @livewireScripts

</body>

</html>

