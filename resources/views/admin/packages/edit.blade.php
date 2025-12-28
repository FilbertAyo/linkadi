<x-admin-layout>
    <div class="pt-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Package</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Update package information</p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <form method="POST" action="{{ route('admin.packages.update', $package) }}" enctype="multipart/form-data" id="packageForm">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6">
                    <!-- Basic Information -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h2>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Package Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $package->name) }}" required 
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Slug</label>
                                <input type="text" name="slug" id="slug" value="{{ old('slug', $package->slug) }}" 
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to auto-generate from name</p>
                                @error('slug')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Package Type *</label>
                                <select name="type" id="type" required 
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                    onchange="togglePricingFields()">
                                    <option value="">Select Type</option>
                                    <option value="nfc_plain" {{ old('type', $package->type) === 'nfc_plain' ? 'selected' : '' }}>NFC Plain Card</option>
                                    <option value="nfc_printed" {{ old('type', $package->type) === 'nfc_printed' ? 'selected' : '' }}>NFC Printed Card</option>
                                    <option value="classic" {{ old('type', $package->type) === 'classic' ? 'selected' : '' }}>Classic Business Cards</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Display Order</label>
                                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', $package->display_order) }}" min="0"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                @error('display_order')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div id="basePriceField" class="md:col-span-2" style="display: none;">
                                <label for="base_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Base Price ($) *</label>
                                <input type="number" name="base_price" id="base_price" value="{{ old('base_price', $package->base_price) }}" step="0.01" min="0"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Single price for NFC card packages</p>
                                @error('base_price')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                                <textarea name="description" id="description" rows="4"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">{{ old('description', $package->description) }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Package Image</label>
                                @if($package->image)
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Current Image:</p>
                                        <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="max-w-xs rounded-lg border border-gray-300 dark:border-gray-600">
                                    </div>
                                @endif
                                <input type="file" name="image" id="image" accept="image/*"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                    onchange="previewImage(this)">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to keep current image</p>
                                <div id="imagePreview" class="mt-4 hidden">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">New Image Preview:</p>
                                    <img id="previewImg" src="" alt="Preview" class="max-w-xs rounded-lg border border-gray-300 dark:border-gray-600">
                                </div>
                                @error('image')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $package->is_active) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active (visible to users)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Features</h2>
                        <div id="featuresContainer">
                            @php
                                $features = old('features', $package->features ?? []);
                            @endphp
                            @if(!empty($features))
                                @foreach($features as $index => $feature)
                                    <div class="flex gap-2 mb-2 feature-item">
                                        <input type="text" name="features[]" value="{{ $feature }}" 
                                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Feature description">
                                        <button type="button" onclick="removeFeature(this)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Remove</button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" onclick="addFeature()" class="mt-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            + Add Feature
                        </button>
                    </div>

                    <!-- Pricing Tiers (for Classic packages) -->
                    <div id="pricingTiersSection" style="display: none;">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Pricing Tiers</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Set quantity ranges and prices for bulk orders</p>
                        <div id="pricingTiersContainer">
                            @if($package->type === 'classic' && $package->pricingTiers->isNotEmpty())
                                @foreach($package->pricingTiers as $index => $tier)
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 p-4 border border-gray-300 dark:border-gray-600 rounded-lg pricing-tier-item">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Min Quantity *</label>
                                            <input type="number" name="pricing_tiers[{{ $index }}][min_quantity]" value="{{ old("pricing_tiers.{$index}.min_quantity", $tier->min_quantity) }}" required min="1"
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Quantity</label>
                                            <input type="number" name="pricing_tiers[{{ $index }}][max_quantity]" value="{{ old("pricing_tiers.{$index}.max_quantity", $tier->max_quantity) }}" min="1"
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                                placeholder="Leave blank for unlimited">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price Per Unit ($) *</label>
                                            <input type="number" name="pricing_tiers[{{ $index }}][price_per_unit]" value="{{ old("pricing_tiers.{$index}.price_per_unit", $tier->price_per_unit) }}" required step="0.01" min="0"
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button" onclick="removePricingTier(this)" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Remove</button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" onclick="addPricingTier()" class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            + Add Pricing Tier
                        </button>
                    </div>
                </div>

                <div class="mt-6 flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Update Package
                    </button>
                    <a href="{{ route('admin.packages.index') }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        let pricingTierCount = {{ $package->pricingTiers->count() ?? 0 }};

        function togglePricingFields() {
            const type = document.getElementById('type').value;
            const basePriceField = document.getElementById('basePriceField');
            const pricingTiersSection = document.getElementById('pricingTiersSection');
            const basePriceInput = document.getElementById('base_price');

            if (type === 'classic') {
                basePriceField.style.display = 'none';
                basePriceInput.removeAttribute('required');
                pricingTiersSection.style.display = 'block';
            } else if (type === 'nfc_plain' || type === 'nfc_printed') {
                basePriceField.style.display = 'block';
                basePriceInput.setAttribute('required', 'required');
                pricingTiersSection.style.display = 'none';
            } else {
                basePriceField.style.display = 'none';
                basePriceInput.removeAttribute('required');
                pricingTiersSection.style.display = 'none';
            }
        }

        function addFeature() {
            const container = document.getElementById('featuresContainer');
            const div = document.createElement('div');
            div.className = 'flex gap-2 mb-2 feature-item';
            div.innerHTML = `
                <input type="text" name="features[]" 
                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                    placeholder="Feature description">
                <button type="button" onclick="removeFeature(this)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Remove</button>
            `;
            container.appendChild(div);
        }

        function removeFeature(button) {
            button.closest('.feature-item').remove();
        }

        function addPricingTier() {
            const container = document.getElementById('pricingTiersContainer');
            const div = document.createElement('div');
            div.className = 'grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 p-4 border border-gray-300 dark:border-gray-600 rounded-lg pricing-tier-item';
            div.innerHTML = `
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Min Quantity *</label>
                    <input type="number" name="pricing_tiers[${pricingTierCount}][min_quantity]" required min="1"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Quantity</label>
                    <input type="number" name="pricing_tiers[${pricingTierCount}][max_quantity]" min="1"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                        placeholder="Leave blank for unlimited">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price Per Unit ($) *</label>
                    <input type="number" name="pricing_tiers[${pricingTierCount}][price_per_unit]" required step="0.01" min="0"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="removePricingTier(this)" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Remove</button>
                </div>
            `;
            container.appendChild(div);
            pricingTierCount++;
        }

        function removePricingTier(button) {
            button.closest('.pricing-tier-item').remove();
        }

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.classList.add('hidden');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            togglePricingFields();
        });
    </script>
</x-admin-layout>

