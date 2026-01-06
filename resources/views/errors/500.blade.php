<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>500 - Server Error | {{ config('app.name', 'Linkadi') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <div class="text-center">
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-red-600">500</h1>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Server Error</h2>
            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                Something went wrong on our end. We're working to fix it.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('welcome') }}" class="px-6 py-3 bg-brand-600 text-white rounded-lg hover:bg-brand-700 font-medium transition-colors">
                    Go Home
                </a>
                <button onclick="window.location.reload()" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition-colors">
                    Try Again
                </button>
            </div>
        </div>
    </div>
</body>
</html>
