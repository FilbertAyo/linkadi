<!-- Top Navigation Bar -->
<header class="fixed z-40 top-0 right-0 bg-white border-b border-gray-200 lg:left-[18rem]">
    <nav class="px-4 py-3 lg:px-6">
        <div class="flex items-center justify-between"> <!-- Page Title / Search Section -->
            <div class="flex items-center space-x-4 flex-1"> @if(!request()->routeIs('admin.dashboard')) <h1 class="text-lg font-semibold text-gray-900"> {{ $pageTitle ?? '' }} </h1> @endif <!-- Search Bar -->
                <div class="hidden md:flex items-center flex-1 max-w-md">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"> <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg> </div> <input type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Search Here">
                    </div>
                </div>
            </div> <!-- Right Section: Notifications, User Info -->
            <div class="flex items-center space-x-3"> <!-- Notifications --> <button type="button" class="relative p-2 text-gray-500 hover:bg-gray-100 rounded-lg"> <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg> <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-600 ring-2 ring-white"></span> </button> <button type="button" class="relative p-2 text-gray-500 hover:bg-gray-100 rounded-lg"> <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg> <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-600 ring-2 ring-white"></span> </button> <!-- User Info -->
                <div class="flex items-center space-x-3 pl-3 border-l border-gray-200">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500">Administrator</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-red-600 flex items-center justify-center text-white font-semibold text-sm"> {{ strtoupper(substr(Auth::user()->name, 0, 2)) }} </div>
                </div>
            </div>
        </div>
    </nav>
</header>