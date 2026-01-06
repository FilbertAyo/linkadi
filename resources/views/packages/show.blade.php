<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $package->name }} - Linkadi</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased font-sans bg-gray-50"> @include('layouts.front-nav') <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8"> <!-- Package Details -->
            <div> @if($package->image) <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="w-full rounded-lg mb-6"> @endif <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $package->name }}</h1> @if($package->description) <p class="text-gray-600 mb-6">{{ $package->description }}</p> @endif @if($package->features && count($package->features) > 0) <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Features</h2>
                    <ul class="space-y-2"> @foreach($package->features as $feature) <li class="flex items-center text-gray-700"> <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg> {{ $feature }} </li> @endforeach </ul>
                </div> @endif @if($package->type === 'classic' && $package->pricingTiers->isNotEmpty()) <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Pricing Tiers</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price Per Card</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200"> @foreach($package->pricingTiers as $tier) <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900"> {{ $tier->min_quantity }}@if($tier->max_quantity) - {{ $tier->max_quantity }}@else+@endif cards </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">TZS {{ number_format($tier->price_per_unit, 2) }}</td>
                                </tr> @endforeach </tbody>
                        </table>
                    </div>
                </div> @endif </div> <!-- Order Form -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Place Your Order</h2> @auth <form method="POST" action="{{ route('orders.store') }}" id="orderForm"> @csrf <input type="hidden" name="package_id" value="{{ $package->id }}"> <!-- Profile Selection (for NFC cards) --> @if($package->type === 'nfc_card' && Auth::user()->profiles()->count() > 0) <div class="mb-6"> <label for="profile_id" class="block text-sm font-medium text-gray-700 mb-2"> Select Profile to Link to This Card * </label> <select name="profile_id" id="profile_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-brand-500"> @foreach(Auth::user()->profiles as $profile) <option value="{{ $profile->id }}" {{ old('profile_id', Auth::user()->primaryProfile?->id) == $profile->id ? 'selected' : '' }}> {{ $profile->profile_name ?? 'Unnamed Profile' }} ({{ $profile->slug }}) @if($profile->is_primary) - Primary @endif </option> @endforeach </select>
                        <p class="mt-1 text-xs text-gray-500"> This NFC card will link to the selected profile. Each card can link to a different profile. </p> @error('profile_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div> @elseif($package->type === 'nfc_card' && Auth::user()->profiles()->count() === 0) <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800 mb-3"> You need to create a profile before ordering an NFC card. </p> <a href="#" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm"> Create Profile </a>
                    </div> @endif @if($package->type === 'classic') <div class="mb-6"> <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2"> Quantity * (Minimum: 100) </label> <input type="number" name="quantity" id="quantity" value="100" min="100" required class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-brand-500" onchange="validateAndCalculatePrice()" onblur="validateAndCalculatePrice()" oninput="calculatePrice()">
                        <p class="mt-1 text-xs text-gray-500">Minimum order quantity is 100 cards</p> @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div> @else <input type="hidden" name="quantity" id="quantity" value="1"> @endif @if($package->type === 'nfc_card') <!-- Card Color Selection for NFC --> @php $availableColors = $package->getAvailableCardColors(); @endphp @if(!empty($availableColors)) <div class="mb-6"> <label class="block text-sm font-medium text-gray-700 mb-2"> Card Color * </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3"> @foreach($availableColors as $color) <label class="relative flex items-center p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors card-color-option {{ old('card_color') === $color ? 'border-brand-500 bg-brand-50' : 'border-gray-300' }}"> <input type="radio" name="card_color" value="{{ $color }}" class="sr-only" {{ old('card_color') === $color ? 'checked' : (($loop->first && !old('card_color')) ? 'checked' : '') }} onchange="calculatePrice()"> <span class="text-sm font-medium text-gray-900 capitalize">{{ $color }}</span> </label> @endforeach </div> @error('card_color') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div> @endif <!-- Printing Option for NFC --> @if($package->supportsPrinting()) <div class="mb-6"> <label class="flex items-center cursor-pointer"> <input type="checkbox" name="requires_printing" id="requires_printing" value="1" {{ old('requires_printing') ? 'checked' : '' }} onchange="calculatePrice()" class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500"> <span class="ml-2 text-sm text-gray-700"> I need printing (+TZS {{ number_format($package->printing_fee ?? 0, 2) }}) </span> </label> @error('requires_printing') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror </div> @endif @endif @if($package->type === 'classic' && $package->requiresDesignOption()) <!-- Design Option for Classic -->
                    <div class="mb-6"> <label class="block text-sm font-medium text-gray-700 mb-2"> Do you have a design ready? * </label>
                        <div class="space-y-2"> <label class="flex items-center cursor-pointer"> <input type="radio" name="has_design" value="1" {{ old('has_design') === '1' ? 'checked' : '' }} onchange="calculatePrice()" class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"> <span class="ml-2 text-sm text-gray-700">Yes, I have my design ready</span> </label> <label class="flex items-center cursor-pointer"> <input type="radio" name="has_design" value="0" {{ old('has_design') === '0' || old('has_design') === null ? 'checked' : '' }} onchange="calculatePrice()" class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"> <span class="ml-2 text-sm text-gray-700">No, I need design service (+TZS {{ number_format($package->design_fee ?? 0, 2) }})</span> </label> </div> @error('has_design') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div> @endif <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="space-y-2">
                            <div class="flex justify-between items-center"> <span class="text-gray-600">Base Price:</span> <span class="font-semibold text-gray-900" id="basePrice">TZS 0.00</span> </div> @if($package->type === 'nfc_card') <div class="flex justify-between items-center" id="subscriptionRow" style="display: none;"> <span class="text-gray-600">Subscription (1 year):</span> <span class="font-semibold text-gray-900" id="subscriptionPrice">TZS 0.00</span> </div>
                            <div class="flex justify-between items-center" id="printingRow" style="display: none;"> <span class="text-gray-600">Printing Fee:</span> <span class="font-semibold text-gray-900" id="printingFee">TZS 0.00</span> </div> @endif @if($package->type === 'classic') <div class="flex justify-between items-center" id="designRow" style="display: none;"> <span class="text-gray-600">Design Fee:</span> <span class="font-semibold text-gray-900" id="designFee">TZS 0.00</span> </div> @if($package->type === 'classic') <div class="flex justify-between items-center"> <span class="text-gray-600">Quantity:</span> <span class="font-semibold text-gray-900" id="displayQuantity">100</span> </div> @endif @endif
                        </div>
                        <hr class="my-3 border-gray-300">
                        <div class="flex justify-between items-center"> <span class="text-lg font-semibold text-gray-900">Total:</span> <span class="text-2xl font-bold text-brand-600" id="totalPrice">TZS 0.00</span> </div>
                    </div>
                    <div class="mb-6"> <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-2"> Shipping Address </label> <textarea name="shipping_address" id="shipping_address" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-brand-500" placeholder="Enter your shipping address"></textarea> @error('shipping_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror </div>
                    <div class="mb-6"> <label for="notes" class="block text-sm font-medium text-gray-700 mb-2"> Additional Notes (Optional) </label> <textarea name="notes" id="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-brand-500" placeholder="Any special instructions or notes"></textarea> @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror </div> <button type="submit" class="w-full px-6 py-3 bg-brand-600 text-white rounded-lg hover:bg-brand-700 font-medium"> Place Order </button>
                </form> @else <div class="text-center py-8">
                    <p class="text-gray-600 mb-4">Please log in to place an order.</p> <a href="{{ route('login') }}" class="inline-block px-6 py-3 bg-brand-600 text-white rounded-lg hover:bg-brand-700 font-medium"> Log In </a>
                </div> @endauth
            </div>
        </div>
    </div>
    <script>
        const packageType = '{{ $package->type }}';
        const basePrice = {
            {
                $package - > base_price ?? 0
            }
        };
        const subscriptionRenewalPrice = {
            {
                $package - > subscription_renewal_price ?? 0
            }
        };
        const printingFee = {
            {
                $package - > printing_fee ?? 0
            }
        };
        const designFee = {
            {
                $package - > design_fee ?? 0
            }
        };
        const pricingTiers = @json($package - > type === 'classic' ? $package - > pricingTiers - > map(function($tier) {
            return ['min' => $tier - > min_quantity, 'max' => $tier - > max_quantity, 'price' => $tier - > price_per_unit];
        }) : []);

        function validateAndCalculatePrice() {
            const quantityInput = document.getElementById('quantity');
            if (!quantityInput) return;
            let quantity = parseInt(quantityInput.value || (packageType === 'classic' ? 100 : 1));
    </script> <!-- Flowbite for mobile menu -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>

</html>