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
    <body class="font-sans antialiased">
            <x-layout> 
                <x-slot:header>
                    <x-layout.header>
                        <x-slot:right>
                            <x-dropdown text="Hello, {{ auth()->user()->name }}!">
                                <form method="POST">
                                    @csrf
                                    <x-dropdown.items text="Logout" onclick="event.preventDefault(); this.closest('form').submit();" />
                                </form>
                            </x-dropdown>
                        </x-slot:right>
                    </x-layout.header>
                </x-slot:header>
                <x-slot:menu>
                    <x-side-bar  smart navigate thin-scroll collapsible>
                        <x-slot:brand>
                            <div class="flex flex-col items-center justify-center sm:pt-12 pt-6">
                                <img src="{{ asset('ocd-avatar.png') }}" alt="Avatar" class="w-15 rounded-full" />
                                <span class="w-[88px]:hidden">
                                    <x-side-bar.separator text="Office of Civil Defense" line-right/> 
                                </span>
                            </div>
                        </x-slot:brand>
                        <x-side-bar.item text="Home" icon="home" :route="route('dashboard')" />
                        <x-side-bar.item text="Supply" icon="cube" :route="route('supply.index')" />
                        <x-side-bar.item text="Stock" icon="hashtag" :route="route('stock.index')" />
                    </x-side-bar>
                </x-slot:menu>
                {{ $slot }}
            </x-layout>
            @livewireScripts
    </body>
</html>
