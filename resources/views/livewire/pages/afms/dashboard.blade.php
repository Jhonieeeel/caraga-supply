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
                        {{-- <p class="py-2 text-xs text-blue-600">Administrative and Financial Management Dashboard</p> --}}
                        <p class="py-2 text-xs text-blue-600">{{ auth()->user()->employee->section->description }}</p>
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
            <x-stats :number="$supplies" icon="cube" title="Supplies" footer="Total number of supplies." animated />
        </div>
        <div class="sm:grid-cols-1">
            <x-stats :number="$stocks" icon="hashtag" title="Stocks"
                footer="Total number of stocks (quantity per item)." animated />
        </div>
        <div class="sm:grid-cols-1">
            <x-stats :number="$requisitions" icon="document" title="Requests" footer="Total number of requests." animated />
        </div>
    </div>
    <div class="w-full bg-white p-3 rounded shadow-md sm:mt-12 border">
        <div class="header">
            <div class="flex justify-start items-center gap-x-6">
                <h4 class="text-sm">Daily Requests</h4>
                <x-badge text="Today" color="green" />
            </div>

            @if ($this->requests->isNotEmpty())
                @foreach ($this->requests as $request)
                    <div class="list">
                        <div class="card flex items-center p-4 bg-white shadow rounded-lg mb-2">
                            <div class="avatar mr-4">
                                {{-- if you have an avatar image, use <img> else use initials/icon --}}
                                <span
                                    class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-600">
                                    {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                </span>
                            </div>
                            <div class="user-info flex-1">
                                <div class="font-medium text-sm text-gray-900">
                                    {{ $request->user->name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Requested at:
                                    {{ $request->created_at->setTimezone('Asia/Manila')->format('h:i A') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="list">
                    <div class="card p-4 bg-white shadow rounded-lg text-sm text-gray-500">
                        No requests today
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

