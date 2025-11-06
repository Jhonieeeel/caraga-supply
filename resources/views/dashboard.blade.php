<x-app-layout>
    <div class="max-w-7xl mx-auto sm:px-3 sm:py-4 lg:px-8">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
        <div class="grid sm:grid-cols-2 sm:my-4 my-3 gap-5">
            <div class="sm:grid-cols-1">
                <div class="flex w-full flex-col rounded-lg bg-blue-100 shadow-md">
                    <div class="mx-2">
                        <p class="p-2 text-xs text-blue-600"></p>
                    </div>
                    <div class="mx-4 flex h-full items-center justify-center gap-4 relative">
                        <div class="grow line-clamp-2">
                            <h2 class="text-2xl font-bold text-blue-500">Welcome Back, {{ auth()->user()->name }}</h2>
                            {{-- user office here --}}
                            <p class="py-2 text-xs text-blue-600">Administrative and Financial Management Dashboard</p>
                        </div>
                        <div class=" absolute right-0 bottom-0">
                            <img src="{{ asset('customer-support.svg') }}" class="w-24 overflow-hidden" alt="">
                        </div>
                    </div>
                    <div class="mx-2">
                        <p class=" p-2 text-xs text-blue-600"></p>
                    </div>
                </div>
            </div>
            <div class="sm:grid-cols-1">
                <x-stats :number="$supplies" icon="cube" title="Supplies" footer="Total number of supplies."
                    animated />
            </div>
            <div class="sm:grid-cols-1">
                <x-stats :number="50" icon="hashtag" title="Stocks"
                    footer="Total number of stocks (quantity per item)." animated />
            </div>
            <div class="sm:grid-cols-1">
                <x-stats :number="50" icon="document" title="Requests" footer="Total number of requests."
                    animated />
            </div>
        </div>
        <div class="w-full bg-white p-3 rounded shadow-md sm:mt-12 border">
            <div class="header">
                <div class="flex justify-start items-center gap-x-6">
                    <h4 class="text-sm">Daily Requests</h4>
                    <x-badge text="Today" />
                </div>
                <div class="list">
                    <div class="card">
                        <div class="avatar"></div>
                        <div class="user-info"></div>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>

