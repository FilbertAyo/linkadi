<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $package->name }} - Linkadi</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans bg-gray-50 dark:bg-gray-900">
    <header class="fixed w-full z-50">
        <nav class="bg-white border-gray-200 py-2.5 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between max-w-screen-xl px-4 mx-auto">
                <a href="{{ route('welcome') }}" class="flex items-center">
                    <img src="{{ asset('images/logo-dark.svg') }}" class="h-6 sm:h-9 dark:hidden" alt="Linkadi Logo" />
                    <img src="{{ asset('images/dark-white.svg') }}" class="h-6 sm:h-9 hidden dark:block" alt="Linkadi Logo" />
                </a>
                <div class="flex items-center">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-800 dark:text-white hover:bg-gray-50 font-medium rounded-lg text-sm px-4 py-2 mr-2">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-800 dark:text-white hover:bg-gray-50 font-medium rounded-lg text-sm px-4 py-2 mr-2">Log in</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Package Details -->
            <div>
                @if($package->image)
                    <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="w-full rounded-lg mb-6">
                @endif

                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ $package->name }}</h1>
                
                @if($package->description)
                    <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $package->description }}</p>
                @endif

                @if($package->features && count($package->features) > 0)
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Features</h2>
                        <ul class="space-y-2">
                            @foreach($package->features as $feature)
                                <li class="flex items-center text-gray-700 dark:text-gray-300">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($package->type === 'classic' && $package->pricingTiers->isNotEmpty())
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Pricing Tiers</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Quantity</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Price Per Card</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($package->pricingTiers as $tier)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                {{ $tier->min_quantity }}@if($tier->max_quantity) - {{ $tier->max_quantity }}@else+@endif cards
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">TZS {{ number_format($tier->price_per_unit, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Order Form -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Place Your Order</h2>

                @auth
                    <form method="POST" action="{{ route('orders.store') }}" id="orderForm">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">

                        <div class="mb-6">
                            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Quantity @if($package->type === 'classic')*@endif
                            </label>
                            <input type="number" 
                                   name="quantity" 
                                   id="quantity" 
                                   value="1" 
                                   min="1"
                                   @if($package->type === 'classic') required @endif
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                   onchange="calculatePrice()"
                                   oninput="calculatePrice()">
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600 dark:text-gray-400">Unit Price:</span>
                                <span class="font-semibold text-gray-900 dark:text-white" id="unitPrice">TZS {{ number_format($package->base_price ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600 dark:text-gray-400">Quantity:</span>
                                <span class="font-semibold text-gray-900 dark:text-white" id="displayQuantity">1</span>
                            </div>
                            <hr class="my-3 border-gray-300 dark:border-gray-600">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">Total:</span>
                                <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400" id="totalPrice">TZS {{ number_format($package->base_price ?? 0, 2) }}</span>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="shipping_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Shipping Address
                            </label>
                            <textarea name="shipping_address" 
                                      id="shipping_address" 
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                      placeholder="Enter your shipping address"></textarea>
                            @error('shipping_address')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Additional Notes (Optional)
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                      placeholder="Any special instructions or notes"></textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                            Place Order
                        </button>
                    </form>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Please log in to place an order.</p>
                        <a href="{{ route('login') }}" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                            Log In
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <script>
        const packageType = '{{ $package->type }}';
        const basePrice = {{ $package->base_price ?? 0 }};
        const pricingTiers = @json($package->type === 'classic' ? $package->pricingTiers->map(function($tier) {
            return [
                'min' => $tier->min_quantity,
                'max' => $tier->max_quantity,
                'price' => $tier->price_per_unit
            ];
        }) : []);

        function calculatePrice() {
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            document.getElementById('displayQuantity').textContent = quantity;

            let unitPrice = basePrice;
            let totalPrice = basePrice * quantity;

            if (packageType === 'classic' && pricingTiers.length > 0) {
                // Find matching tier
                for (let tier of pricingTiers) {
                    if (quantity >= tier.min && (tier.max === null || quantity <= tier.max)) {
                        unitPrice = tier.price;
                        totalPrice = tier.price * quantity;
                        break;
                    }
                }
            }

            document.getElementById('unitPrice').textContent = 'TZS ' + unitPrice.toFixed(2);
            document.getElementById('totalPrice').textContent = 'TZS ' + totalPrice.toFixed(2);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculatePrice();
        });
    </script>
</body>
</html>

