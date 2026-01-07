<x-client-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Page Header -->
        <div>
            <a href="{{ route('profile.select-package', $profile) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Package Selection
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Order {{ $package->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">Complete your order for "{{ $profile->profile_name }}" profile</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h3 class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</h3>
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.create-order', $profile) }}">
            @csrf
            <input type="hidden" name="package_id" value="{{ $package->id }}">
            
            <div class="space-y-6">
                <!-- Subscription Duration -->
                @php $subscriptionOptions = $package->getSubscriptionOptions(); @endphp
                @if(count($subscriptionOptions) > 1)
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Choose Subscription Duration</h3>
                        <p class="text-sm text-gray-500 mb-4">Save more with longer subscriptions!</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($subscriptionOptions as $index => $option)
                                <label class="relative flex flex-col p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-brand-400 subscription-option {{ $option['years'] === 1 ? 'border-brand-500 bg-brand-50' : 'border-gray-300' }}" data-years="{{ $option['years'] }}" data-price="{{ $option['price'] }}" data-savings="{{ $option['savings'] ?? 0 }}">
                                    <input type="radio" name="subscription_years" value="{{ $option['years'] }}" {{ $option['years'] === 1 ? 'checked' : '' }} required class="sr-only" onchange="updatePrice()">
                                    @if($option['label'])
                                        <span class="absolute top-0 right-0 -mt-2 -mr-2 px-2 py-1 bg-brand-600 text-white text-xs font-medium rounded-full">
                                            {{ $option['label'] }}
                                        </span>
                                    @endif
                                    <div class="text-xl font-bold text-gray-900 mb-1">
                                        {{ $option['years'] }} {{ Str::plural('Year', $option['years']) }}
                                    </div>
                                    <div class="text-2xl font-bold text-brand-600 mb-1">
                                        {{ number_format($option['price']) }} TZS
                                    </div>
                                    @if($option['savings'] > 0)
                                        <div class="text-sm text-green-600 font-medium">
                                            Save {{ number_format($option['savings']) }} TZS
                                        </div>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                @else
                    <input type="hidden" name="subscription_years" value="1">
                @endif

                <!-- Card Configuration -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Card Configuration</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Card Color *</label>
                            <select name="card_color" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Select Color</option>
                                <option value="black">Black</option>
                                <option value="white">White</option>
                                <option value="silver">Silver</option>
                                <option value="gold">Gold</option>
                                <option value="blue">Blue</option>
                            </select>
                        </div>

                        @if($package->printing_fee > 0)
                            <div>
                                <label class="flex items-start">
                                    <input type="checkbox" name="requires_printing" value="1" id="requiresPrinting" class="rounded border-gray-300 text-brand-600 mt-0.5" onchange="updatePrice()">
                                    <span class="ml-2">
                                        <span class="text-sm font-medium text-gray-700">
                                            Custom Printing (+{{ number_format($package->printing_fee) }} TZS)
                                        </span>
                                        <input type="text" name="printing_text" id="printingText" placeholder="Text to print on card" maxlength="255" class="mt-2 w-full rounded-md border-gray-300 text-sm" disabled>
                                    </span>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Shipping Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-2">
                                Delivery Address *
                            </label>
                            <textarea name="shipping_address" id="shipping_address" rows="3" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Enter your full delivery address"></textarea>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Additional Notes (Optional)
                            </label>
                            <textarea name="notes" id="notes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Any special instructions"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
                    
                    <dl class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">NFC Card + <span id="subscriptionYearsText">1 Year</span> Subscription</dt>
                            <dd class="font-medium text-gray-900" id="basePrice">{{ number_format($subscriptionOptions[0]['price']) }} TZS</dd>
                        </div>
                        
                        <div id="printingRow" class="flex justify-between text-sm" style="display: none;">
                            <dt class="text-gray-600">Custom Printing</dt>
                            <dd class="font-medium text-gray-900">{{ number_format($package->printing_fee) }} TZS</dd>
                        </div>
                        
                        <div id="savingsRow" class="flex justify-between text-sm text-green-600" style="display: none;">
                            <dt class="font-medium">Multi-Year Savings</dt>
                            <dd class="font-bold" id="savings">0 TZS</dd>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex justify-between">
                                <dt class="text-base font-medium text-gray-900">Total</dt>
                                <dd class="text-xl font-bold text-brand-600" id="totalPrice">
                                    {{ number_format($subscriptionOptions[0]['price']) }} TZS
                                </dd>
                            </div>
                        </div>
                        
                        <div class="text-xs text-gray-500 pt-2 space-y-1">
                            <p>✓ Physical NFC card + QR code</p>
                            <p>✓ Profile active for <span id="subscriptionDuration">1 year</span></p>
                            <p>✓ Free delivery</p>
                            <p>✓ Delivered in 5-7 business days</p>
                        </div>
                    </dl>

                    <button type="submit" class="mt-6 w-full px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg transition-colors">
                        Proceed to Payment
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const printingFee = {{ $package->printing_fee ?? 0 }};
        const subscriptionOptions = @json($subscriptionOptions);

        document.getElementById('requiresPrinting')?.addEventListener('change', function() {
            const printingText = document.getElementById('printingText');
            if (printingText) {
                printingText.disabled = !this.checked;
                if (!this.checked) printingText.value = '';
            }
        });

        document.querySelectorAll('input[name="subscription_years"]').forEach(radio => {
            radio.addEventListener('change', updatePrice);
        });

        document.querySelectorAll('.subscription-option').forEach(option => {
            option.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    updatePrice();
                }
            });
        });

        function updatePrice() {
            const selectedYearsRadio = document.querySelector('input[name="subscription_years"]:checked');
            const selectedYears = selectedYearsRadio ? parseInt(selectedYearsRadio.value) : 1;
            const option = subscriptionOptions.find(opt => opt.years === selectedYears);
            
            if (!option) return;

            const basePrice = parseFloat(option.price);
            const requiresPrinting = document.getElementById('requiresPrinting')?.checked || false;
            const printing = requiresPrinting ? printingFee : 0;
            const total = basePrice + printing;
            const savings = option.savings ?? 0;

            document.getElementById('basePrice').textContent = basePrice.toLocaleString() + ' TZS';
            document.getElementById('totalPrice').textContent = total.toLocaleString() + ' TZS';
            document.getElementById('subscriptionYearsText').textContent = selectedYears + ' ' + (selectedYears > 1 ? 'Years' : 'Year');
            document.getElementById('subscriptionDuration').textContent = selectedYears + ' ' + (selectedYears > 1 ? 'years' : 'year');

            document.getElementById('printingRow').style.display = printing > 0 ? 'flex' : 'none';
            document.getElementById('savingsRow').style.display = savings > 0 ? 'flex' : 'none';
            if (savings > 0) {
                document.getElementById('savings').textContent = savings.toLocaleString() + ' TZS';
            }

            // Update option styles
            document.querySelectorAll('.subscription-option').forEach(opt => {
                const isSelected = parseInt(opt.dataset.years) === selectedYears;
                opt.classList.toggle('border-brand-500', isSelected);
                opt.classList.toggle('bg-brand-50', isSelected);
                opt.classList.toggle('border-gray-300', !isSelected);
            });
        }

        // Initialize
        updatePrice();
    </script>
</x-client-layout>

