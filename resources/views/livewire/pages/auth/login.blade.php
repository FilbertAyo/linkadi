<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')]
class extends Component
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

        // Redirect admins to admin dashboard, others to regular dashboard
        $redirectRoute = auth()->user()->hasRole('admin')
            ? route('admin.dashboard', absolute: false)
            : route('dashboard', absolute: false);

        $this->redirectIntended(default: $redirectRoute, navigate: true);
    }
}; ?>

<div>
    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">
        Sign in to platform
    </h2>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 text-sm text-green-600 bg-green-50 p-3 rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="mt-8 space-y-6">
        <!-- Email Address -->
        <div>
            <label for="email" class="text-sm font-medium text-gray-900 block mb-2">Your email</label>
            <input wire:model="form.email" id="email" type="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-brand-600 focus:border-brand-600 block w-full p-2.5" placeholder="name@company.com" required autofocus autocomplete="username">
            @error('form.email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="text-sm font-medium text-gray-900 block mb-2">Your password</label>
            <input wire:model="form.password" id="password" type="password" name="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-brand-600 focus:border-brand-600 block w-full p-2.5" required autocomplete="current-password">
            @error('form.password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input wire:model="form.remember" id="remember" aria-describedby="remember" name="remember" type="checkbox" class="bg-gray-50 border-gray-300 focus:ring-3 focus:ring-brand-200 h-4 w-4 rounded text-brand-600">
            </div>
            <div class="text-sm ml-3">
                <label for="remember" class="font-medium text-gray-900">Remember me</label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-brand-600 hover:underline ml-auto" wire:navigate>Lost Password?</a>
            @endif
        </div>

        <button type="submit" class="text-white bg-brand-600 hover:bg-brand-700 focus:ring-4 focus:ring-brand-200 font-medium rounded-lg text-base px-5 py-3 w-full sm:w-auto text-center">
            Login to your account
        </button>

        <div class="text-sm font-medium text-gray-500">
            Not registered?
            <a href="{{ route('register') }}" class="text-brand-600 hover:underline" wire:navigate>Create account</a>
        </div>
    </form>
</div>
