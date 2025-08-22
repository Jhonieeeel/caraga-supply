<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input label="Email *" wire:model="form.email" id="email" class="block w-full" type="email"/>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input label="Password *" wire:model="form.password" id="password" class="block w-full"
                            type="password" required autocomplete="current-password"/>
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <x-checkbox label="Remember Me" wire:model="form.remember"/>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif
            <x-button submit class="ms-3" sm text="Login" />
        </div>
    </form>
</div>
