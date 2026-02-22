<header class="fixed w-full z-50 border-b border-gray-100">

        <nav class="bg-white border-gray-200 py-2.5">
            <div class="flex flex-wrap items-center justify-between max-w-screen-xl px-4 mx-auto">
                <a href="{{ route('welcome') }}" class="flex items-center">
                    <img src="{{ asset('images/logo-dark.svg') }}" class="h-6 sm:h-9" alt="Linkadi Logo" />
                </a>
                <div class="flex items-center lg:order-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-800 hover:bg-gray-50 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 sm:mr-2 focus:outline-none">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-800 hover:bg-gray-50 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 sm:mr-2 focus:outline-none">Log in</a>
                        <a href="{{ route('register') }}" class="text-white bg-brand-600 hover:bg-brand-700 focus:ring-4 focus:ring-brand-200 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 sm:mr-2 lg:mr-0">Get started</a>
                    @endauth
                    <button data-collapse-toggle="mobile-menu-2" type="button" class="inline-flex items-center p-2 ml-1 text-sm text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200" aria-controls="mobile-menu-2" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
                <div class="items-center justify-between hidden w-full lg:flex lg:w-auto lg:order-1" id="mobile-menu-2">
                    <ul class="flex flex-col mt-4 font-medium lg:flex-row lg:space-x-8 lg:mt-0">
                   
                        <li>
                        <x-nav-link href="/#features">Features</x-nav-link>
                        </li>
                        <li>
                        <x-nav-link href="/#how-it-works">How it Works</x-nav-link>
                        </li>
                        <li>
                        <x-nav-link href="/#packages">Packages</x-nav-link>
                        </li>
                       
                        <li>
                        <x-nav-link href="/#faq">FAQ</x-nav-link>
                        </li>
                    </ul>
                </div>
                        </div>
        </nav>
                    </header>