// Package form management
// pricingTierCount should be set before this script loads
// If not set, it will default to 0
if (typeof pricingTierCount === 'undefined') {
    pricingTierCount = 0;
}

function togglePricingFields() {
    const type = document.getElementById('type').value;
    const basePriceField = document.getElementById('basePriceField');
    const nfcPricingFields = document.getElementById('nfcPricingFields');
    const classicPricingFields = document.getElementById('classicPricingFields');
    const pricingTiersSection = document.getElementById('pricingTiersSection');
    const basePriceInput = document.getElementById('base_price');
    
    if (type === 'classic') {
        basePriceField.style.display = 'none';
        nfcPricingFields.style.display = 'none';
        classicPricingFields.style.display = 'block';
        if (basePriceInput) basePriceInput.removeAttribute('required');
        pricingTiersSection.style.display = 'block';
    } else if (type === 'nfc_card') {
        basePriceField.style.display = 'block';
        nfcPricingFields.style.display = 'block';
        classicPricingFields.style.display = 'none';
        if (basePriceInput) basePriceInput.setAttribute('required', 'required');
        pricingTiersSection.style.display = 'none';
    } else {
        basePriceField.style.display = 'none';
        nfcPricingFields.style.display = 'none';
        classicPricingFields.style.display = 'none';
        if (basePriceInput) basePriceInput.removeAttribute('required');
        pricingTiersSection.style.display = 'none';
    }
}

function addCardColor() {
    const container = document.getElementById('cardColorsContainer');
    const div = document.createElement('div');
    div.className = 'flex gap-2 card-color-item';
    div.innerHTML = `
        <input type="text" name="card_colors[]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="Color name (e.g., white, black, blue)">
        <button type="button" onclick="removeCardColor(this)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Remove</button>
    `;
    container.appendChild(div);
}

function removeCardColor(button) {
    button.closest('.card-color-item').remove();
}

function addFeature() {
    const container = document.getElementById('featuresContainer');
    const div = document.createElement('div');
    div.className = 'flex gap-2 mb-2 feature-item';
    div.innerHTML = `
        <input type="text" name="features[]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="Feature description">
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
    div.className = 'grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 p-4 border border-gray-300 rounded-lg pricing-tier-item';
    div.innerHTML = `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Min Quantity *</label>
            <input type="number" name="pricing_tiers[${pricingTierCount}][min_quantity]" required min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Max Quantity</label>
            <input type="number" name="pricing_tiers[${pricingTierCount}][max_quantity]" min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="Leave blank for unlimited">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Price Per Unit (TZS) *</label>
            <input type="number" name="pricing_tiers[${pricingTierCount}][price_per_unit]" required step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500">
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
