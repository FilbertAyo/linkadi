<x-admin-layout>
    <div class="pt-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create New Package</h1>
            <p class="text-gray-600 mt-2">Add a new package to the system</p>
        </div>
        <div class="bg-white shadow rounded-lg border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.packages.store') }}" enctype="multipart/form-data" id="packageForm"> @csrf <div class="grid grid-cols-1 gap-6"> <!-- Basic Information -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h2>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div> <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Package Name *</label> <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500"> @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror </div>
                            <div> <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug</label> <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="Auto-generated from name">
                                <p class="mt-1 text-xs text-gray-500">Leave blank to auto-generate from name</p> @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div> <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Package Type *</label> <select name="type" id="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" onchange="togglePricingFields()">
                                    <option value="">Select Type</option>
                                    <option value="nfc_card" {{ old('type') === 'nfc_card' ? 'selected' : '' }}>NFC Card</option>
                                    <option value="classic" {{ old('type') === 'classic' ? 'selected' : '' }}>Classic Business Cards</option>
                                </select> @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror </div>
                            <div> <label for="display_order" class="block text-sm font-medium text-gray-700 mb-2">Display Order</label> <input type="number" name="display_order" id="display_order" value="{{ old('display_order', 0) }}" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500"> @error('display_order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror </div>
                            <div id="basePriceField" class="md:col-span-2" style="display: none;"> <label for="base_price" class="block text-sm font-medium text-gray-700 mb-2">Base Price (TZS) *</label> <input type="number" name="base_price" id="base_price" value="{{ old('base_price') }}" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500">
                                <p class="mt-1 text-xs text-gray-500">Card price (includes 1 year subscription for NFC cards)</p> @error('base_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div id="nfcPricingFields" class="md:col-span-2" style="display: none;">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div> <label for="subscription_renewal_price" class="block text-sm font-medium text-gray-700 mb-2">Subscription Renewal Price (TZS)</label> <input type="number" name="subscription_renewal_price" id="subscription_renewal_price" value="{{ old('subscription_renewal_price') }}" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500">
                                        <p class="mt-1 text-xs text-gray-500">Annual renewal price after first year</p> @error('subscription_renewal_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div> <label for="printing_fee" class="block text-sm font-medium text-gray-700 mb-2">Printing Fee (TZS)</label> <input type="number" name="printing_fee" id="printing_fee" value="{{ old('printing_fee') }}" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500">
                                        <p class="mt-1 text-xs text-gray-500">Additional fee if printing is required</p> @error('printing_fee') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="mt-4"> <label for="card_colors" class="block text-sm font-medium text-gray-700 mb-2">Available Card Colors</label>
                                    <div id="cardColorsContainer" class="space-y-2"> @if(old('card_colors')) @foreach(old('card_colors') as $index => $color) <div class="flex gap-2 card-color-item"> <input type="text" name="card_colors[]" value="{{ $color }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="Color name (e.g., white, black, blue)"> <button type="button" onclick="removeCardColor(this)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Remove</button> </div> @endforeach @endif </div> <button type="button" onclick="addCardColor()" class="mt-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"> + Add Color </button> @error('card_colors') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div id="classicPricingFields" class="md:col-span-2" style="display: none;">
                                <div> <label for="design_fee" class="block text-sm font-medium text-gray-700 mb-2">Design Fee (TZS)</label> <input type="number" name="design_fee" id="design_fee" value="{{ old('design_fee') }}" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500">
                                    <p class="mt-1 text-xs text-gray-500">Fee charged if client doesn't have their own design</p> @error('design_fee') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="md:col-span-2"> <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label> <textarea name="description" id="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea> @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror </div>
                            <div class="md:col-span-2"> <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Package Image</label> <input type="file" name="image" id="image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" onchange="previewImage(this)">
                                <div id="imagePreview" class="mt-4 hidden"> <img id="previewImg" src="" alt="Preview" class="max-w-xs rounded-lg border border-gray-300"> </div> @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2"> <label class="flex items-center"> <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> <span class="ml-2 text-sm text-gray-700">Active (visible to users)</span> </label> </div>
                        </div>
                    </div> <!-- Features -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Features</h2>
                        <div id="featuresContainer"> @if(old('features')) @foreach(old('features') as $index => $feature) <div class="flex gap-2 mb-2 feature-item"> <input type="text" name="features[]" value="{{ $feature }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="Feature description"> <button type="button" onclick="removeFeature(this)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Remove</button> </div> @endforeach @endif </div> <button type="button" onclick="addFeature()" class="mt-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"> + Add Feature </button>
                    </div> <!-- Pricing Tiers (for Classic packages) -->
                    <div id="pricingTiersSection" style="display: none;">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Pricing Tiers</h2>
                        <p class="text-sm text-gray-600 mb-4">Set quantity ranges and prices for bulk orders</p>
                        <div id="pricingTiersContainer"> <!-- Pricing tiers will be added dynamically --> </div> <button type="button" onclick="addPricingTier()" class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"> + Add Pricing Tier </button>
                    </div>
                </div>
                <div class="mt-6 flex gap-4"> <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"> Create Package </button> <a href="{{ route('admin.packages.index') }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500"> Cancel </a> </div>
            </form>
        </div>
    </div>
    <script>
        // Initialize pricingTierCount for create form
        var pricingTierCount = 0;
    </script>
    <script src="{{ asset('js/package-form.js') }}"></script>
</x-admin-layout>