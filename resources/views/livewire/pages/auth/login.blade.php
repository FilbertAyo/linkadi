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
    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
        Sign in to platform
    </h2>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 text-sm text-green-600 bg-green-50 dark:bg-green-900/20 dark:text-green-400 p-3 rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="mt-8 space-y-6">
        <!-- Email Address -->
        <div>
            <label for="email" class="text-sm font-medium text-gray-900 dark:text-white block mb-2">Your email</label>
            <input wire:model="form.email" 
                   id="email" 
                   type="email" 
                   name="email" 
                   class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white sm:text-sm rounded-lg focus:ring-brand-600 focus:border-brand-600 dark:focus:ring-brand-500 dark:focus:border-brand-500 block w-full p-2.5" 
                   placeholder="name@company.com" 
                   required 
                   autofocus 
                   autocomplete="username">
            @error('form.email')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="text-sm font-medium text-gray-900 dark:text-white block mb-2">Your password</label>
            <input wire:model="form.password" 
                   id="password" 
                   type="password" 
                   name="password" 
                   placeholder="••••••••" 
                   class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white sm:text-sm rounded-lg focus:ring-brand-600 focus:border-brand-600 dark:focus:ring-brand-500 dark:focus:border-brand-500 block w-full p-2.5" 
                   required 
                   autocomplete="current-password">
            @error('form.password')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input wire:model="form.remember" 
                       id="remember" 
                       aria-describedby="remember" 
                       name="remember" 
                       type="checkbox" 
                       class="bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 focus:ring-3 focus:ring-brand-200 dark:focus:ring-brand-700 h-4 w-4 rounded text-brand-600">
            </div>
            <div class="text-sm ml-3">
                <label for="remember" class="font-medium text-gray-900 dark:text-white">Remember me</label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-brand-600 dark:text-brand-400 hover:underline ml-auto" wire:navigate>Lost Password?</a>
            @endif
        </div>

        <button type="submit" class="text-white bg-brand-600 hover:bg-brand-700 dark:bg-brand-600 dark:hover:bg-brand-600 focus:ring-4 focus:ring-brand-200 dark:focus:ring-brand-700 font-medium rounded-lg text-base px-5 py-3 w-full sm:w-auto text-center">
            Login to your account
        </button>

        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Not registered? <a href="{{ route('register') }}" class="text-brand-600 dark:text-brand-400 hover:underline" wire:navigate>Create account</a>
        </div>
    </form>
</div>
