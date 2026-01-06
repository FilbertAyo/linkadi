<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Linkadi') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <main class="bg-gray-50">
        <div class="mx-auto md:h-screen flex flex-col justify-center items-center px-6 pt-8 pt:mt-0">
            <div class="flex justify-between items-center w-full sm:max-w-screen-sm mb-8 lg:mb-10">
                <a href="{{ route('welcome') }}" class="text-2xl font-semibold flex justify-center items-center">
                    <img src="{{ asset('images/logo-dark.svg') }}" class="h-10" alt="Linkadi Logo">
                </a>
            </div>
            
            <!-- Card -->
            <div class="bg-white shadow rounded-lg md:mt-0 w-full sm:max-w-screen-sm xl:p-0 border border-gray-200">
                <div class="p-6 sm:p-8 lg:p-16 space-y-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </main>
</body>
</html>