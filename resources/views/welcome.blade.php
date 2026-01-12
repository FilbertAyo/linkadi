<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Linkadi - Digital Identity & NFC Business Cards</title>

    <!-- Meta SEO -->
    <meta name="description" content="Linkadi is a digital identity and NFC business card platform. Create your digital profile once, share it with a tap using NFC cards or QR codes.">
    <meta name="robots" content="index, follow">
    <meta name="language" content="English">
    <meta name="author" content="Linkadi">

    <!-- Social media share -->
    <meta property="og:title" content="Linkadi - Digital Identity & NFC Business Cards">
    <meta property="og:site_name" content="Linkadi">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:description" content="Create your digital identity once, share it forever with NFC cards or QR codes. Update your profile anytime without reprogramming your card.">
    <meta property="og:type" content="website">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased font-sans">

     @include('layouts.landing.navigation')

    <!-- Hero Section -->
    <section class="bg-white">
        <div class="grid max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
            <div class="mr-auto place-self-center lg:col-span-7">
                <h1 class="max-w-2xl mb-4 text-4xl font-extrabold leading-none tracking-tight md:text-5xl xl:text-6xl text-gray-900">
                    Your digital identity.<br>One tap away.
                </h1>
                <p class="max-w-2xl mb-6 font-light text-gray-500 lg:mb-8 md:text-lg lg:text-xl">
                    Linkadi is a digital identity platform powered by NFC technology. Create your profile once, share it with a simple tap. Your NFC card stores only a URL—your profile lives online and updates in real-time.
                </p>
                <div class="space-y-4 sm:flex sm:space-y-0 sm:space-x-4">
                    @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center w-full px-5 py-3 text-sm font-medium text-center text-white bg-brand-600 rounded-lg sm:w-auto hover:bg-brand-700 focus:ring-4 focus:ring-brand-200">
                        Go to Dashboard
                        <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                    @else
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center w-full px-5 py-3 text-sm font-medium text-center text-white bg-brand-600 rounded-lg sm:w-auto hover:bg-brand-700 focus:ring-4 focus:ring-brand-200">
                        Create Your Profile
                        <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                    <a href="#how-it-works" class="inline-flex items-center justify-center w-full px-5 py-3 text-sm font-medium text-center text-gray-900 border border-gray-200 rounded-lg sm:w-auto hover:bg-gray-100 focus:ring-4 focus:ring-gray-100">
                        Learn More
                    </a>
                    @endauth
                </div>
            </div>
            <div class="hidden lg:mt-0 lg:col-span-5 lg:flex">
                <img src="{{ asset('images/hero.png') }}" alt="Linkadi NFC Card Illustration">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="bg-gray-50">
        <div class="max-w-screen-xl px-4 py-8 mx-auto space-y-12 lg:space-y-20 lg:py-24 lg:px-6">
            <!-- Feature 1 -->
            <div class="items-center gap-8 lg:grid lg:grid-cols-2 xl:gap-16">
                <div class="text-gray-500 sm:text-lg">
                    <h2 class="mb-4 text-3xl font-extrabold tracking-tight text-gray-900">One Profile, Endless Connections</h2>
                    <p class="mb-8 font-light lg:text-xl">Create your digital identity once with all your contact information, social links, and professional details. Share it instantly with anyone who taps your NFC card or scans your QR code.</p>
                    <ul role="list" class="pt-8 space-y-5 border-t border-gray-200 my-7">
                        <li class="flex space-x-3">
                            <svg class="flex-shrink-0 w-5 h-5 text-brand-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-base font-medium leading-tight text-gray-900">Update your profile anytime</span>
                        </li>
                        <li class="flex space-x-3">
                            <svg class="flex-shrink-0 w-5 h-5 text-brand-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-base font-medium leading-tight text-gray-900">No need to reprogram your card</span>
                        </li>
                        <li class="flex space-x-3">
                            <svg class="flex-shrink-0 w-5 h-5 text-brand-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-base font-medium leading-tight text-gray-900">Control your privacy settings</span>
                        </li>
                    </ul>
                </div>
                <img class="hidden w-full mb-4 rounded-lg lg:mb-0 lg:flex" src="{{ asset('images/feature-1.png') }}" alt="Digital Profile Feature">
            </div>
            <!-- Feature 2 -->
            <div class="items-center gap-8 lg:grid lg:grid-cols-2 xl:gap-16">
                <img class="hidden w-full mb-4 rounded-lg lg:mb-0 lg:flex lg:order-first" src="{{ asset('images/feature-2.png') }}" alt="NFC Card Feature">
                <div class="text-gray-500 sm:text-lg">
                    <h2 class="mb-4 text-3xl font-extrabold tracking-tight text-gray-900">Simple NFC Technology</h2>
                    <p class="mb-8 font-light lg:text-xl">Our NFC cards store only a URL—no personal data. When someone taps your card, it opens your live profile instantly. Simple, secure, and always up-to-date.</p>
                    <ul role="list" class="pt-8 space-y-5 border-t border-gray-200 my-7">
                        <li class="flex space-x-3">
                            <svg class="flex-shrink-0 w-5 h-5 text-brand-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-base font-medium leading-tight text-gray-900">Works with any NFC-enabled device</span>
                        </li>
                        <li class="flex space-x-3">
                            <svg class="flex-shrink-0 w-5 h-5 text-brand-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-base font-medium leading-tight text-gray-900">Instant profile sharing</span>
                        </li>
                        <li class="flex space-x-3">
                            <svg class="flex-shrink-0 w-5 h-5 text-brand-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-base font-medium leading-tight text-gray-900">QR code included for universal access</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="bg-white">
        <div class="max-w-screen-xl px-4 py-8 mx-auto lg:py-24 lg:px-6">
            <div class="max-w-screen-md mx-auto mb-8 text-center lg:mb-12">
                <h2 class="mb-4 text-3xl font-extrabold tracking-tight text-gray-900">How It Works</h2>
                <p class="mb-5 font-light text-gray-500 sm:text-xl">Get started with your digital identity in three simple steps</p>
            </div>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="text-center">
                    <div class="flex justify-center items-center mb-4 w-10 h-10 rounded-full bg-brand-100 lg:h-12 lg:w-12">
                        <span class="text-2xl font-bold text-brand-600">1</span>
                    </div>
                    <h3 class="mb-2 text-xl font-bold">Create Your Profile</h3>
                    <p class="text-gray-500">Sign up and build your digital profile with your contact information, social links, and professional details.</p>
                </div>
                <div class="text-center">
                    <div class="flex justify-center items-center mb-4 w-10 h-10 rounded-full bg-brand-100 lg:h-12 lg:w-12">
                        <span class="text-2xl font-bold text-brand-600">2</span>
                    </div>
                    <h3 class="mb-2 text-xl font-bold">Order Your NFC Card</h3>
                    <p class="text-gray-500">Choose your card design and order. We'll program it with your unique profile URL and ship it to you.</p>
                </div>
                <div class="text-center">
                    <div class="flex justify-center items-center mb-4 w-10 h-10 rounded-full bg-brand-100 lg:h-12 lg:w-12">
                        <span class="text-2xl font-bold text-brand-600">3</span>
                    </div>
                    <h3 class="mb-2 text-xl font-bold">Share & Update</h3>
                    <p class="text-gray-500">Tap to share your profile. Update your information anytime—your card stays the same, your profile evolves.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="bg-gray-50">
        <div class="max-w-screen-xl px-4 py-8 mx-auto lg:py-24 lg:px-6">
            <div class="max-w-screen-md mx-auto mb-8 text-center lg:mb-12">
                <h2 class="mb-4 text-3xl font-extrabold tracking-tight text-gray-900">Choose Your Package</h2>
                <p class="mb-5 font-light text-gray-500 sm:text-xl">Select the perfect NFC card package for your needs</p>
            </div>

            @if($packages->isEmpty())
            <div class="text-center py-12">
                <p class="text-gray-500">No packages available at the moment. Please check back later.</p>
            </div>
            @else
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach($packages->flatten() as $package)
                <div class="flex flex-col p-6 mx-auto max-w-lg text-center text-gray-900 bg-white border border-gray-200 rounded-lg shadow xl:p-8">
                    @if($package->image)
                    <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="w-full h-48 object-cover rounded-lg mb-4">
                    @endif

                    <h3 class="mb-4 text-2xl font-semibold">{{ $package->name }}</h3>

                    @if($package->description)
                    <p class="font-light text-gray-500 sm:text-lg mb-4">{{ Str::limit($package->description, 100) }}</p>
                    @endif

                    <div class="flex justify-center items-baseline my-8">
                        @if($package->type === 'classic')
                        @if($package->pricingTiers->isNotEmpty())
                        <span class="mr-2 text-4xl font-extrabold">From TZS {{ number_format($package->pricingTiers->first()->price_per_unit, 2) }}</span>
                        <span class="text-gray-500">/card</span>
                        @endif
                        @else
                        <span class="mr-2 text-4xl font-extrabold">TZS {{ number_format($package->base_price ?? 0, 2) }}</span>
                        @endif
                    </div>

                    @if($package->features && count($package->features) > 0)
                    <ul role="list" class="mb-8 space-y-4 text-left">
                        @foreach($package->features as $feature)
                        <li class="flex items-center space-x-3">
                            <svg class="flex-shrink-0 w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    <a href="{{ route('dashboard.cards.packages') }}" class="text-white bg-brand-600 hover:bg-brand-700 focus:ring-4 focus:ring-brand-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        @auth
                        Order Now
                        @else
                        Get Started
                        @endauth
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </section>

    <!-- CTA Section -->
    <section class="white">
        <div class="max-w-screen-xl px-4 py-8 mx-auto lg:py-16 lg:px-6">
            <div class="max-w-screen-sm mx-auto text-center">
                <h2 class="mb-4 text-3xl font-extrabold leading-tight tracking-tight text-gray-900">Ready to create your digital identity?</h2>
                <p class="mb-6 font-light text-gray-500 md:text-lg">Join Linkadi today and get your NFC card. No credit card required to start.</p>
                @auth
                <a href="{{ route('dashboard') }}" class="text-white bg-brand-600 hover:bg-brand-700 focus:ring-4 focus:ring-brand-200 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none">Go to Dashboard</a>
                @else
                <a href="{{ route('register') }}" class="text-white bg-brand-600 hover:bg-brand-700 focus:ring-4 focus:ring-brand-200 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none">Get Started Free</a>
                @endauth
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="bg-gray-50 pt-16">
        <div class="max-w-screen-xl px-4 pb-8 mx-auto lg:pb-24 lg:px-6">
            <h2 class="mb-6 text-3xl font-extrabold tracking-tight text-center text-gray-900 lg:mb-8 lg:text-3xl">Frequently asked questions</h2>
            <div class="max-w-screen-md mx-auto">
                <div id="accordion-flush" data-accordion="collapse" data-active-classes="bg-white text-gray-900" data-inactive-classes="text-gray-500">
                    <h3 id="accordion-flush-heading-1">
                        <button type="button" class="flex items-center justify-between w-full py-5 font-medium text-left text-gray-900 bg-white border-b border-gray-200" data-accordion-target="#accordion-flush-body-1" aria-expanded="true" aria-controls="accordion-flush-body-1">
                            <span>How do NFC cards work?</span>
                            <svg data-accordion-icon class="w-6 h-6 rotate-180 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </h3>
                    <div id="accordion-flush-body-1" class="" aria-labelledby="accordion-flush-heading-1">
                        <div class="py-5 border-b border-gray-200">
                            <p class="mb-2 text-gray-500">NFC (Near Field Communication) cards store a URL that opens when tapped by an NFC-enabled device. Your Linkadi card contains only your profile URL—no personal data is stored on the card itself. When someone taps it, their device opens your live profile page.</p>
                        </div>
                    </div>
                    <h3 id="accordion-flush-heading-2">
                        <button type="button" class="flex items-center justify-between w-full py-5 font-medium text-left text-gray-500 border-b border-gray-200" data-accordion-target="#accordion-flush-body-2" aria-expanded="false" aria-controls="accordion-flush-body-2">
                            <span>Can I update my profile after getting my card?</span>
                            <svg data-accordion-icon class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </h3>
                    <div id="accordion-flush-body-2" class="hidden" aria-labelledby="accordion-flush-heading-2">
                        <div class="py-5 border-b border-gray-200">
                            <p class="mb-2 text-gray-500">Yes! That's one of the main benefits of Linkadi. Your profile lives online and can be updated anytime through your dashboard. Changes are reflected instantly—no need to reprogram your NFC card or print new QR codes.</p>
                        </div>
                    </div>
                    <h3 id="accordion-flush-heading-3">
                        <button type="button" class="flex items-center justify-between w-full py-5 font-medium text-left text-gray-500 border-b border-gray-200" data-accordion-target="#accordion-flush-body-3" aria-expanded="false" aria-controls="accordion-flush-body-3">
                            <span>What devices can read NFC cards?</span>
                            <svg data-accordion-icon class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </h3>
                    <div id="accordion-flush-body-3" class="hidden" aria-labelledby="accordion-flush-heading-3">
                        <div class="py-5 border-b border-gray-200">
                            <p class="mb-2 text-gray-500">Most modern smartphones (iPhone 7 and later, Android devices with NFC) can read NFC cards. For devices without NFC, we also provide QR codes that work with any device with a camera. Your profile is accessible to everyone, regardless of their device.</p>
                        </div>
                    </div>
                    <h3 id="accordion-flush-heading-4">
                        <button type="button" class="flex items-center justify-between w-full py-5 font-medium text-left text-gray-500 border-b border-gray-200" data-accordion-target="#accordion-flush-body-4" aria-expanded="false" aria-controls="accordion-flush-body-4">
                            <span>Is my data secure?</span>
                            <svg data-accordion-icon class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </h3>
                    <div id="accordion-flush-body-4" class="hidden" aria-labelledby="accordion-flush-heading-4">
                        <div class="py-5 border-b border-gray-200">
                            <p class="mb-2 text-gray-500">Yes. Your NFC card stores only a URL—no personal information. Your profile data is securely stored on our servers, and you have full control over what information is visible. You can toggle visibility for any contact method or social link.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    @include('layouts.landing.footer')

    <!-- Flowbite JS for accordion -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>

</body>

</html>