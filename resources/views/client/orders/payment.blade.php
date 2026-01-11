<x-client-layout>
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Page Header -->
        <div>
            <a href="{{ route('dashboard.orders.show', $order) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Order
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Complete Payment</h2>
            <p class="mt-1 text-sm text-gray-500">Order #{{ $order->id }}</p>
        </div>

        <!-- Order Summary -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
            @php
                $breakdown = $order->pricing_breakdown ?? [];
                $cardConfigs = $breakdown['card_configurations'] ?? [];
                $subscriptionYears = $order->subscription_years ?? 1;
                $isRenewal = $order->profile_id !== null;
                $renewalProfile = $isRenewal ? \App\Models\Profile::find($order->profile_id) : null;
                $durationDays = ($order->package->subscription_duration_days ?? 365) * $subscriptionYears;
            @endphp

            @if($isRenewal && $renewalProfile)
                <!-- Renewal Order Summary -->
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm font-medium text-blue-800">🔄 Subscription Renewal Order</p>
                    <p class="text-xs text-blue-600 mt-1">Renewing: {{ $renewalProfile->profile_name ?? $renewalProfile->slug }}</p>
                </div>
            @endif

            <dl class="space-y-2">
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-600">
                        @if($isRenewal)
                            Subscription Renewal - {{ $subscriptionYears }} {{ Str::plural('Year', $subscriptionYears) }}
                        @else
                            {{ $order->quantity }} × {{ $order->package->name }}
                            @if($subscriptionYears > 1)
                                <span class="text-xs text-brand-600">({{ $subscriptionYears }} {{ Str::plural('year', $subscriptionYears) }})</span>
                            @endif
                        @endif
                    </dt>
                    <dd class="font-medium text-gray-900">{{ number_format($order->base_price) }} TZS</dd>
                </div>

                @if($order->printing_fee > 0)
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-600">
                            Printing Fees
                            @php
                                $printingCount = collect($cardConfigs)->where('requires_printing', true)->count();
                            @endphp
                            @if($printingCount > 0)
                                <span class="text-xs">({{ $printingCount }} {{ Str::plural('card', $printingCount) }})</span>
                            @endif
                        </dt>
                        <dd class="font-medium text-gray-900">{{ number_format($order->printing_fee) }} TZS</dd>
                    </div>
                @endif

                @if($order->subscription_discount > 0)
                    <div class="flex justify-between text-sm text-green-600">
                        <dt class="font-medium">Multi-Year Discount</dt>
                        <dd class="font-medium">-{{ number_format($order->subscription_discount) }} TZS</dd>
                    </div>
                @endif

                <div class="border-t border-gray-200 pt-2 mt-2">
                    <div class="flex justify-between">
                        <dt class="text-base font-medium text-gray-900">Total to Pay</dt>
                        <dd class="text-lg font-bold text-brand-600">{{ number_format($order->total_price) }} TZS</dd>
                    </div>
                </div>
            </dl>

            <div class="mt-4 pt-4 border-t border-gray-200 text-sm text-gray-500 space-y-1">
                @if($isRenewal)
                    <p>✓ Renewing subscription for {{ $subscriptionYears }} {{ Str::plural('year', $subscriptionYears) }}</p>
                    @if($renewalProfile && $renewalProfile->expires_at)
                        <p>✓ Current expiration: {{ $renewalProfile->expires_at->format('M d, Y') }}</p>
                        @php
                            $newExpiration = $renewalProfile->expires_at->copy();
                            if ($newExpiration->isFuture()) {
                                $newExpiration->addDays($durationDays);
                            } else {
                                $newExpiration = now()->addDays($durationDays);
                            }
                        @endphp
                        <p>✓ New expiration: {{ $newExpiration->format('M d, Y') }}</p>
                    @endif
                @else
                    <p>✓ {{ $order->quantity }} {{ Str::plural('Card', $order->quantity) }} with {{ $subscriptionYears }}-{{ Str::plural('year', $subscriptionYears) }} subscription</p>
                    <p>✓ Free shipping & delivery</p>
                    <p>✓ Cards delivered in 5-7 business days</p>
                    @if(count($cardConfigs) > 0)
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <p class="font-medium text-gray-700 mb-2">Card Details:</p>
                            @foreach($cardConfigs as $index => $config)
                                @php
                                    $profile = \App\Models\Profile::find($config['profile_id']);
                                @endphp
                                @if($profile)
                                    <p class="text-xs">
                                        • Card {{ $index + 1 }}: {{ $profile->profile_name }}
                                        <span class="text-gray-400">• {{ ucfirst($config['card_color']) }}</span>
                                        @if($config['requires_printing'])
                                            <span class="text-gray-400">• Printed</span>
                                        @endif
                                    </p>
                                @endif
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Select Payment Method</h3>

            <div class="space-y-4">
                <!-- Online Payment - Coming Soon -->
                <div class="border-2 border-gray-200 rounded-lg p-4 bg-gray-50 opacity-75">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-sm font-semibold text-gray-900">Online Payment</h4>
                            <p class="text-xs text-gray-500">M-Pesa, Tigo Pesa, Airtel Money</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Coming Soon
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Manual Payment -->
                <div class="border-2 border-brand-500 rounded-lg p-6 bg-brand-50">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-lg bg-brand-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Manual Payment</h4>
                            <p class="text-sm text-gray-600 mb-4">
                                Call us to arrange payment. Payment will be collected upon delivery of your cards.
                            </p>
                            
                            <div class="space-y-3">
                                <div class="bg-white rounded-lg p-4 border border-brand-200">
                                    <p class="text-xs font-medium text-gray-500 mb-2">Call us at:</p>
                                    <div class="space-y-2">
                                        <a href="tel:+255755237692" class="flex items-center text-brand-600 hover:text-brand-700 font-medium">
                                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            +255 755 237 692
                                        </a>
                                        <a href="tel:+255742615246" class="flex items-center text-brand-600 hover:text-brand-700 font-medium">
                                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            +255 742 615 246
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <p class="text-xs text-blue-800">
                                        <strong>Note:</strong> Please mention your order number ({{ $order->id }}) when calling. 
                                        Our team will arrange delivery and payment collection.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-client-layout> 
