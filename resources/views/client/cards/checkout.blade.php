<x-client-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Page Header -->
        <div>
            <a href="{{ route('dashboard.cards.packages') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Packages
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Order {{ $package->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">Configure your NFC card order</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('dashboard.cards.store') }}" id="checkoutForm" onsubmit="console.log('Form submitting...', new FormData(this));">
            @csrf
            <input type="hidden" name="package_id" value="{{ $package->id }}">
            <input type="hidden" name="quantity" id="hiddenQuantity" value="1">

            <div class="space-y-6">
                <!-- Subscription Duration Selector (1 year only - 2 and 3 year options removed) -->
                @php
                    $subscriptionOptions = collect($package->getSubscriptionOptions())->where('years', 1)->values()->all();
                @endphp

                @if($package->enable_multi_year_subscriptions && count($subscriptionOptions) >= 1)
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Choose Subscription Duration</h3>
                        <p class="text-sm text-gray-500 mb-4">1 year subscription included.</p>
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($subscriptionOptions as $index => $option)
                                <label class="relative flex flex-col p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-brand-400 subscription-option {{ $option['years'] === 1 ? 'border-brand-500 bg-brand-50' : 'border-gray-300' }}" data-years="{{ $option['years'] }}" data-price="{{ $option['price'] }}">
                                    <input type="radio" name="subscription_years" value="{{ $option['years'] }}" {{ $option['years'] === 1 ? 'checked' : '' }} class="sr-only subscription-radio" onchange="updateSubscriptionSelection({{ $option['years'] }}, {{ $option['price'] }})">
                                    @if($option['label'])
                                        <span class="absolute top-0 right-0 -mt-2 -mr-2 px-2 py-1 bg-brand-600 text-white text-xs font-medium rounded-full">
                                            {{ $option['label'] }}
                                        </span>
                                    @endif
                                    <div class="flex items-baseline justify-between mb-2">
                                        <span class="text-2xl font-bold text-gray-900">
                                            {{ $option['years'] }} {{ Str::plural('Year', $option['years']) }}
                                        </span>
                                    </div>
                                    <div class="text-lg font-semibold text-brand-600 mb-1">
                                        {{ number_format($option['price']) }} TZS
                                    </div>
                                    @if($option['savings'] > 0)
                                        <div class="text-sm text-green-600 font-medium">
                                            Save {{ number_format($option['savings']) }} TZS ({{ $option['savings_percentage'] }}%)
                                        </div>
                                    @endif
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ number_format($option['price_per_year']) }} TZS/year
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @else
                    <input type="hidden" name="subscription_years" value="1">
                @endif

                <!-- Quantity Selector -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">How many cards do you need?</h3>
                    <div class="flex items-center space-x-4">
                        <label for="quantity" class="text-sm font-medium text-gray-700">Quantity:</label>
                        <select id="quantity" onchange="updateCards()" class="rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">{{ $i }} {{ Str::plural('card', $i) }}</option>
                            @endfor
                        </select>
                        <span class="text-sm text-gray-500">(Maximum 10 cards per order)</span>
                    </div>

                    <!-- Bulk Configuration Option -->
                    <div id="bulkConfigSection" class="mt-4 hidden">
                        <label class="flex items-start">
                            <input type="checkbox" id="useSameConfig" class="rounded border-gray-300 text-brand-600 shadow-sm focus:border-brand-500 focus:ring-brand-500 mt-0.5" onchange="toggleBulkConfig()">
                            <span class="ml-2">
                                <span class="text-sm font-medium text-gray-700">Use same details for all cards</span>
                                <p class="text-xs text-gray-500">Apply the same profile, color, and printing options to all cards</p>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Card Configuration -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configure Your Cards</h3>
                    <div class="space-y-6" id="cardsContainer">
                        <!-- Cards will be dynamically added here -->
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Shipping Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-2">Delivery Address *</label>
                            <textarea name="shipping_address" id="shipping_address" rows="3" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Enter your full delivery address"></textarea>
                            @error('shipping_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Additional Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Any special instructions"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">
                                <span id="summaryQuantityLabel"><span id="summaryQuantity">1</span> × {{ number_format($package->base_price) }} TZS</span>
                            </dt>
                            <dd class="font-medium text-gray-900" id="summaryBaseTotal">
                                {{ number_format($package->base_price) }} TZS
                            </dd>
                        </div>
                        <div id="summaryPrintingRow" class="flex justify-between text-sm" style="display: none;">
                            <dt class="text-gray-600">Printing Fees</dt>
                            <dd class="font-medium text-gray-900" id="summaryPrintingTotal">0 TZS</dd>
                        </div>
                        <div id="summarySavingsRow" class="flex justify-between text-sm text-green-600" style="display: none;">
                            <dt class="font-medium">Multi-Year Savings</dt>
                            <dd class="font-bold" id="summarySavings">0 TZS</dd>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex justify-between">
                                <dt class="text-base font-medium text-gray-900">Total</dt>
                                <dd class="text-base font-bold text-gray-900" id="summaryTotal">
                                    {{ number_format($package->base_price) }} TZS
                                </dd>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 pt-2">
                            <p id="summarySubscriptionInfo">✓ Includes 1-year subscription for <span id="summaryQuantity2">1</span> profile</p>
                            <p>✓ Free delivery</p>
                            <p>✓ Cards delivered in 5-7 business days</p>
                        </div>
                    </dl>
                    <button type="submit" id="submitBtn" class="mt-6 w-full px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-lg transition-colors">
                        Order Now
                    </button>
                    @if($errors->any())
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-600">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- External JavaScript -->
    <script src="{{ asset('js/card-checkout.js') }}"></script>
    
    <!-- Initialize with Blade data -->
    <script>
        @php
            $subscriptionOptions = collect($package->getSubscriptionOptions())->where('years', 1)->values()->all();
        @endphp
        
        if (typeof initCardCheckout === 'function') {
            initCardCheckout({
                printingFee: {{ $package->printing_fee ?? 0 }},
                subscriptionOptions: @json($subscriptionOptions),
                profiles: @json($availableProfiles->values()),
                availableColors: @json($package->getAvailableCardColors())
            });
        }
    </script>
</x-client-layout>
