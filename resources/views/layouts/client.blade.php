<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Linkadi') }} - Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Smooth Scrolling -->
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="h-full bg-gray-50">
    <!-- Flash Messages -->
    <x-flash-messages />
    
    <!-- Top Navigation (Landing Page Style) -->
    @include('layouts.clients.navigation')

    <!-- Mobile sidebar backdrop -->
    <div class="bg-gray-900 opacity-50 hidden fixed top-16 left-0 right-0 bottom-0 z-20 lg:hidden" id="sidebarBackdrop"></div>

    <div class="bg-gray-50 pt-16">
        <div class="max-w-screen-xl px-4 mx-auto">
            <div class="flex gap-4 lg:gap-6 py-4">
                <!-- Sidebar -->
                @include('layouts.clients.aside')
                <!-- Main content -->
                <div id="main-content" class="flex-1 min-w-0 bg-white border border-gray-200 rounded-lg shadow-sm">
                    <main class="p-6 lg:p-8">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar toggle script -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        const toggleSidebarMobileEl = document.getElementById('toggleSidebarMobile');
        const toggleSidebarMobileHamburger = document.getElementById('toggleSidebarMobileHamburger');
        const toggleSidebarMobileClose = document.getElementById('toggleSidebarMobileClose');

        const toggleSidebarMobile = () => {
            if (sidebar.classList.contains('hidden')) {
                sidebar.classList.remove('hidden');
                sidebar.classList.add('flex');
                sidebarBackdrop.classList.remove('hidden');
                if (toggleSidebarMobileHamburger) toggleSidebarMobileHamburger.classList.add('hidden');
                if (toggleSidebarMobileClose) toggleSidebarMobileClose.classList.remove('hidden');
            } else {
                sidebar.classList.add('hidden');
                sidebar.classList.remove('flex');
                sidebarBackdrop.classList.add('hidden');
                if (toggleSidebarMobileHamburger) toggleSidebarMobileHamburger.classList.remove('hidden');
                if (toggleSidebarMobileClose) toggleSidebarMobileClose.classList.add('hidden');
            }
        }

        if (toggleSidebarMobileEl) {
            toggleSidebarMobileEl.addEventListener('click', toggleSidebarMobile);
        }

        const sidebarCloseButton = document.getElementById('sidebarCloseButton');
        if (sidebarCloseButton) {
            sidebarCloseButton.addEventListener('click', toggleSidebarMobile);
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', toggleSidebarMobile);
        }
    </script>

    <!-- Flowbite JS for mobile menu -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>

    <!-- Alpine.js for dropdowns -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  
</body>
</html>
