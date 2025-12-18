<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
        Confirm your password
    </h2>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400 mt-4">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form wire:submit="confirmPassword" class="mt-8 space-y-6">
        <!-- Password -->
        <div>
            <label for="password" class="text-sm font-medium text-gray-900 dark:text-white block mb-2">Your password</label>
            <input wire:model="password"
                   id="password"
                   type="password"
                   name="password"
                   placeholder="••••••••"
                   class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white sm:text-sm rounded-lg focus:ring-brand-600 focus:border-brand-600 dark:focus:ring-brand-500 dark:focus:border-brand-500 block w-full p-2.5"
                   required 
                   autocomplete="current-password">
            @error('password')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="text-white bg-brand-600 hover:bg-brand-700 dark:bg-brand-600 dark:hover:bg-brand-600 focus:ring-4 focus:ring-brand-200 dark:focus:ring-brand-700 font-medium rounded-lg text-base px-5 py-3 w-full sm:w-auto text-center">
            {{ __('Confirm') }}
        </button>
    </form>
</div>
