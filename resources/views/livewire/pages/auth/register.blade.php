<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
        Create a Free Account
    </h2>

    <form wire:submit="register" class="mt-8 space-y-6">
        <!-- Name -->
        <div>
            <label for="name" class="text-sm font-medium text-gray-900 dark:text-white block mb-2">Your name</label>
            <input wire:model="name" 
                   id="name" 
                   type="text" 
                   name="name" 
                   class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white sm:text-sm rounded-lg focus:ring-brand-600 focus:border-brand-600 dark:focus:ring-brand-500 dark:focus:border-brand-500 block w-full p-2.5" 
                   placeholder="John Doe" 
                   required 
                   autofocus 
                   autocomplete="name">
            @error('name')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="text-sm font-medium text-gray-900 dark:text-white block mb-2">Your email</label>
            <input wire:model="email" 
                   id="email" 
                   type="email" 
                   name="email" 
                   class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white sm:text-sm rounded-lg focus:ring-brand-600 focus:border-brand-600 dark:focus:ring-brand-500 dark:focus:border-brand-500 block w-full p-2.5" 
                   placeholder="name@company.com" 
                   required 
                   autocomplete="username">
            @error('email')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

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
                   autocomplete="new-password">
            @error('password')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="text-sm font-medium text-gray-900 dark:text-white block mb-2">Confirm password</label>
            <input wire:model="password_confirmation" 
                   id="password_confirmation" 
                   type="password" 
                   name="password_confirmation" 
                   placeholder="••••••••" 
                   class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white sm:text-sm rounded-lg focus:ring-brand-600 focus:border-brand-600 dark:focus:ring-brand-500 dark:focus:border-brand-500 block w-full p-2.5" 
                   required 
                   autocomplete="new-password">
            @error('password_confirmation')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="text-white bg-brand-600 hover:bg-brand-700 dark:bg-brand-600 dark:hover:bg-brand-600 focus:ring-4 focus:ring-brand-200 dark:focus:ring-brand-700 font-medium rounded-lg text-base px-5 py-3 w-full sm:w-auto text-center">
            Create account
        </button>

        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Already have an account? <a href="{{ route('login') }}" class="text-brand-600 dark:text-brand-400 hover:underline" wire:navigate>Login here</a>
        </div>
    </form>
</div>
